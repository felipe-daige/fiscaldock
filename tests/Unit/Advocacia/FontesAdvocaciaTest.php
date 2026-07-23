<?php

use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Fontes\Advocacia\CeatTrtFonte;
use App\Services\Consultas\Fontes\Advocacia\CertidaoStjFonte;
use App\Services\Consultas\Fontes\Advocacia\ProtestosFonte;

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

test('as 11 fontes advocacia estao registradas e prontas', function () {
    config()->set('consultas.fontes_pausadas', []); // baseline: nada pausado na origem
    $registry = app(FonteRegistry::class);
    $chaves = ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf',
        'certidao_tcu', 'improbidade', 'ceis', 'cnep', 'protestos', 'falencias'];

    foreach ($chaves as $chave) {
        $fonte = $registry->get($chave);
        expect($fonte)->not->toBeNull("fonte {$chave} ausente do registry")
            ->and($fonte->pronta())->toBeTrue()
            ->and($fonte->provider())->toBe('infosimples'); // herda o throttle 1 req/s do job
    }
});

test('catalogo avulso expoe 20 fontes em 4 grupos (TJMS 2-etapas + cadastro gratis + analise fiscal)', function () {
    config()->set('consultas.fontes_pausadas', []); // baseline: nada pausado na origem
    $grupos = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class)->grupos();

    expect(array_keys($grupos))->toBe(['judicial', 'integridade', 'passivo', 'fiscal'])
        ->and(count($grupos['judicial']['fontes']))->toBe(6) // + certidao_tjms (fase 4)
        ->and(count($grupos['integridade']['fontes']))->toBe(4)
        ->and(count($grupos['passivo']['fontes']))->toBe(2)
        ->and(count($grupos['fiscal']['fontes']))->toBe(8); // + cadastro (grátis) + analise_fiscal (paga)

    // R$ 1,00 default em todas as single-call. Exceção: fonte de 2 ETAPAS custa ≥2 chamadas pagas
    // ao provedor (pedido + conferências), então tem override em `advocacia.precos`.
    $overrides = (array) config('advocacia.precos', []);
    foreach ($grupos as $g) {
        foreach ($g['fontes'] as $f) {
            expect($f['preco'])->toBe((float) ($overrides[$f['chave']] ?? 1.00));
        }
    }

    expect($overrides['certidao_tjms'] ?? null)->toBe(2.00); // 2 etapas não cabe no R$ 1,00
});

test('etapas dinamicas incluem os grupos novos em ordem canonica', function () {
    $catalogo = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class);

    $etapas = $catalogo->etapasDe(['certidao_stj', 'protestos', 'ceis', 'cnd_federal']);

    expect(array_column($etapas, 'chave'))->toBe([
        'inicializacao', 'cadastrais', 'certidoes_federais', 'certidoes_judiciais', 'integridade', 'passivo',
    ])->and(array_column($etapas, 'numero'))->toBe([1, 2, 3, 4, 5, 6]);
});

test('certidao STJ normaliza sucesso negativa e 611 indeterminado', function () {
    $fonte = new CertidaoStjFonte;

    $ok = $fonte->normalizar(['data' => [[
        'tipo' => 'Negativa', 'numero_certidao' => 'STJ-123', 'emissao_data' => '21/07/2026',
        'site_receipt' => 'https://infosimples/receipt.pdf',
    ]]], 'sucesso');
    expect($ok['certidao_stj']['status'])->toBe('Negativa')
        ->and($ok['certidao_stj']['certidao_codigo'])->toBe('STJ-123')
        ->and($ok['certidao_stj']['comprovante'])->toBe('https://infosimples/receipt.pdf')
        ->and($ok['consultas_realizadas'])->toBe(['certidao_stj']);

    $ind = $fonte->normalizar(['code_message' => 'dados incompletos'], 'indeterminado');
    expect($ind['certidao_stj']['status'])->toBe('INDETERMINADO');

    expect($fonte->normalizar(['code' => 605], 'retry'))->toBe([]);
});

test('CEAT resolve o TRT pela UF, exige nome+cpf_solicitante e manda nome+cnpj+cpf_solicitante nos params', function () {
    $fonte = new CeatTrtFonte;

    expect($fonte->slugPara(['uf' => 'RJ']))->toBe('tribunal/trt1/ceat')
        ->and($fonte->slugPara(['uf' => 'MS']))->toBe('tribunal/trt24/ceat')
        ->and($fonte->slugPara(['uf' => 'AP']))->toBe('tribunal/trt8/ceat')
        // TRT6 (PE) é a exceção de slug: `certidao`, não `ceat`.
        ->and($fonte->slugPara(['uf' => 'PE']))->toBe('tribunal/trt6/certidao')
        // Aplicável exige UF + razão social + CPF do solicitante (do dono da conta, no alvo).
        ->and($fonte->aplicavelPara(['uf' => 'MG', 'razao_social' => 'ACME LTDA', 'cpf_solicitante' => '11144477735']))->toBeTrue()
        // `nome` obrigatório (606 billable sem ele — smoke lote 260): sem razão social → INDISPONIVEL.
        ->and($fonte->aplicavelPara(['uf' => 'MG', 'cpf_solicitante' => '11144477735']))->toBeFalse()
        ->and($fonte->aplicavelPara(['uf' => '', 'razao_social' => 'ACME', 'cpf_solicitante' => '11144477735']))->toBeFalse()
        ->and($fonte->aplicavelPara([]))->toBeFalse();

    // Params carregam nome + cnpj + o cpf_solicitante do alvo (dono da conta, injetado pelo job).
    expect($fonte->params(['cnpj' => '19.131.243/0001-97', 'razao_social' => 'ACME LTDA', 'cpf_solicitante' => '111.444.777-35']))
        ->toBe(['cnpj' => '19131243000197', 'nome' => 'ACME LTDA', 'cpf_solicitante' => '11144477735']);

    // Sem cpf_solicitante no alvo (usuário sem users.cpf), a fonte fica INDISPONIVEL sem chamar nem
    // cobrar — NÃO há mais fallback de CPF de sistema (evita emitir certidão em nome de terceiro).
    expect($fonte->aplicavelPara(['uf' => 'MS', 'razao_social' => 'ACME LTDA']))->toBeFalse()
        ->and($fonte->params(['cnpj' => '19131243000197', 'razao_social' => 'ACME LTDA']))
        ->toBe(['cnpj' => '19131243000197', 'nome' => 'ACME LTDA', 'cpf_solicitante' => ''])
        ->and($fonte->motivoIndisponivel(['uf' => 'MS', 'razao_social' => 'ACME LTDA']))
        ->toContain('CPF do solicitante');

    // Sem UF/nome o job persiste INDISPONIVEL com o motivo — sem chamada nem cobrança.
    $bloco = $fonte->normalizar(['_motivo' => $fonte->motivoIndisponivel([])], 'nao_aplicavel');
    expect($bloco['ceat_trt']['status'])->toBe('INDISPONIVEL');
});

test('CEAT em SP separa TRT2 (Grande SP/Baixada) de TRT15 (interior) pelo município', function () {
    $fonte = new CeatTrtFonte;

    $sp = fn (string $municipio) => $fonte->slugPara(['uf' => 'SP', 'municipio' => $municipio]);

    // Jurisdição do TRT2: capital, Grande SP, ABC, Baixada Santista, Ibiúna.
    expect($sp('SAO PAULO'))->toBe('tribunal/trt2/ceat')
        ->and($sp('São Bernardo do Campo'))->toBe('tribunal/trt2/ceat')
        ->and($sp('GUARULHOS'))->toBe('tribunal/trt2/ceat')
        ->and($sp('SANTOS'))->toBe('tribunal/trt2/ceat')
        ->and($sp('CUBATÃO'))->toBe('tribunal/trt2/ceat')
        ->and($sp('IBIÚNA'))->toBe('tribunal/trt2/ceat')
        // Todo o resto do estado é TRT15/Campinas — inclusive a Baixada que ficou de fora do TRT2.
        ->and($sp('RIBEIRAO PRETO'))->toBe('tribunal/trt15/ceat')
        ->and($sp('Ribeirão Preto'))->toBe('tribunal/trt15/ceat')
        ->and($sp('CAMPINAS'))->toBe('tribunal/trt15/ceat')
        ->and($sp('SOROCABA'))->toBe('tribunal/trt15/ceat')
        ->and($sp('SÃO JOSÉ DOS CAMPOS'))->toBe('tribunal/trt15/ceat')
        ->and($sp('PERUÍBE'))->toBe('tribunal/trt15/ceat')
        ->and($sp('ITANHAÉM'))->toBe('tribunal/trt15/ceat')
        ->and($sp('MONGAGUÁ'))->toBe('tribunal/trt15/ceat');

    // Homônimo de outro estado não vaza pro mapa de SP (São Paulo só existe como SP aqui).
    expect($fonte->slugPara(['uf' => 'MG', 'municipio' => 'SANTOS DUMONT']))->toBe('tribunal/trt3/ceat');

    $alvo = ['uf' => 'SP', 'razao_social' => 'ACME LTDA', 'cpf_solicitante' => '11144477735'];

    // Sem município NÃO chuta o TRT: certidão da região errada volta negativa falsa.
    expect($fonte->trtPara($alvo))->toBeNull()
        ->and($fonte->aplicavelPara($alvo))->toBeFalse()
        ->and($fonte->motivoIndisponivel($alvo))->toContain('município')
        ->and($fonte->slugPara($alvo))->toBe('tribunal/trt{n}/ceat')
        ->and($fonte->aplicavelPara($alvo + ['municipio' => 'RIBEIRAO PRETO']))->toBeTrue()
        ->and($fonte->trtPara($alvo + ['municipio' => 'RIBEIRAO PRETO']))->toBe(15);
});

test('CEAT fica indisponível no TRT22 (PI), que não publica CEAT', function () {
    $fonte = new CeatTrtFonte;
    $alvo = ['uf' => 'PI', 'razao_social' => 'ACME LTDA', 'cpf_solicitante' => '11144477735'];

    expect($fonte->trtPara($alvo))->toBeNull()
        ->and($fonte->aplicavelPara($alvo))->toBeFalse()
        ->and($fonte->motivoIndisponivel($alvo))->toContain('TRT22');
});

test('CEAT normaliza o contrato real (nada consta, processos do CNPJ, expedicao)', function () {
    $bloco = (new CeatTrtFonte)->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => false,
        'nada_consta' => false,
        'numero_certidao' => '7654321',
        'normalizado_expedicao_datahora' => '20/07/2026 14:30:00',
        'total_processos' => 6,
        'processos_encontrados_cpf_cnpj' => ['quantidade' => 2, 'lista_processos' => ['111', '222']],
        'processos_encontrados_nome' => ['quantidade' => 4, 'lista_processos' => ['a', 'b', 'c', 'd']],
    ]]], 'sucesso');

    expect($bloco['ceat_trt']['status'])->toBe('Positiva')
        ->and($bloco['ceat_trt']['certidao_codigo'])->toBe('7654321')
        ->and($bloco['ceat_trt']['emissao_data'])->toBe('20/07/2026')
        ->and($bloco['ceat_trt']['processos_cnpj_quantidade'])->toBe(2)
        ->and($bloco['ceat_trt']['processos_cnpj'])->toBe(['111', '222'])
        ->and($bloco['ceat_trt']['total_processos'])->toBe(6);
});

test('MPT exige UF, manda uf nos params e deriva status de nada_consta + procedimentos', function () {
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CertidaoMptFonte;

    expect($fonte->aplicavelPara(['uf' => 'SP']))->toBeTrue()
        ->and($fonte->aplicavelPara(['uf' => '']))->toBeFalse()
        ->and($fonte->params(['cnpj' => '19131243000197', 'uf' => 'sp']))
        ->toBe(['cnpj' => '19131243000197', 'uf' => 'SP']);

    $bloco = $fonte->normalizar(['data' => [[
        'nada_consta' => false,
        'titulo' => 'CERTIDÃO DE FEITOS PARA FINS GERAIS',
        'codigo' => '111',
        'procedimentos' => [
            ['ano_autuacao' => '2024', 'classe' => 'PAJ', 'numero' => '123', 'normalizado_numero' => '0123', 'situacao' => 'ATIVO', 'partes_polo_passivo' => ['X']],
        ],
    ]]], 'sucesso');

    expect($bloco['certidao_mpt']['status'])->toBe('Positiva')
        ->and($bloco['certidao_mpt']['total_procedimentos'])->toBe(1)
        ->and($bloco['certidao_mpt']['procedimentos'][0])->toBe(['ano_autuacao' => '2024', 'classe' => 'PAJ', 'numero' => '123', 'situacao' => 'ATIVO']);

    $negativa = $fonte->normalizar(['data' => [['nada_consta' => true, 'procedimentos' => []]]], 'sucesso');
    expect($negativa['certidao_mpt']['status'])->toBe('Negativa');
});

test('TRF unificada manda tipo+email e deriva status da conjuncao dos TRFs', function () {
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CertidaoTrfFonte;

    config()->set('advocacia.email_solicitante', 'consultas@fiscaldock.com.br');
    expect($fonte->params(['cnpj' => '19.131.243/0001-97']))
        ->toBe(['cnpj' => '19131243000197', 'tipo' => '1', 'email' => 'consultas@fiscaldock.com.br']);

    // Todos os TRFs conseguiram emitir negativa → Negativa (regular).
    $neg = $fonte->normalizar(['data' => [[
        'conseguiu_emitir' => true, 'emitiu_pdf' => true,
        'detalhes_certidao' => [
            'numero_certidao' => 'TRF-123', 'codigo_validacao' => 'ABC',
            'normalizado_data_hora_emissao' => '25/11/2022 16:09:17',
            'tribunais' => [
                'trf1' => ['conseguiu_emitir_certidao_negativa' => true],
                'trf2' => ['conseguiu_emitir_certidao_negativa' => true],
                'trf3' => ['conseguiu_emitir_certidao_negativa' => true],
            ],
        ],
    ]]], 'sucesso');
    expect($neg['certidao_trf']['status'])->toBe('Negativa')
        ->and($neg['certidao_trf']['certidao_codigo'])->toBe('TRF-123')
        ->and($neg['certidao_trf']['emissao_data'])->toBe('25/11/2022')
        ->and($neg['certidao_trf']['tribunais_com_feitos'])->toBe([]);

    // Um TRF sem negativa (constam feitos) → Positiva, apontando qual.
    $pos = $fonte->normalizar(['data' => [[
        'detalhes_certidao' => ['tribunais' => [
            'trf1' => ['conseguiu_emitir_certidao_negativa' => true],
            'trf3' => ['conseguiu_emitir_certidao_negativa' => false],
        ]],
    ]]], 'sucesso');
    expect($pos['certidao_trf']['status'])->toBe('Positiva')
        ->and($pos['certidao_trf']['tribunais_com_feitos'])->toBe(['TRF3']);

    // Async do CJF (lote 261): pedido aceito, sem `tribunais`, "em andamento por e-mail em 6h".
    // Vira estado EM_ANDAMENTO (não status nulo silencioso) e mensagem honesta (sem "veja seu e-mail").
    $and = $fonte->normalizar(['data' => [[
        'conseguiu_emitir' => false,
        'mensagem' => 'Solicitação ainda em andamento! Aguarde a disponibilização da certidão por email em até 06 horas.',
        'detalhes_certidao' => [],
    ]]], 'sucesso');
    expect($and['certidao_trf']['status'])->toBe('Em andamento')
        ->and($and['certidao_trf']['certidao_codigo'])->toBeNull()
        ->and($and['certidao_trf']['mensagem'])->not->toContain('email')
        ->and($and['certidao_trf']['mensagem'])->toContain('CJF');
});

test('TRF: certidao EMITIDA nunca vira "Em andamento", mesmo com frase parecida', function () {
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CertidaoTrfFonte;

    // Marcar como pendente uma certidão que SAIU é caro: cobra, o CertidaoRegistro pula o registro
    // (EM ANDAMENTO está em STATUS_NAO_EMITIDA), não nasce valida_ate/alerta e o card fica pendente
    // pra sempre — não há polling no TRF. 'disponibiliza' é a redação de SUCESSO do CJF e por isso
    // saiu da lista de gatilhos; qualquer marca de emissão também desqualifica.
    $emitida = $fonte->normalizar(['data' => [[
        'mensagem' => 'Certidão negativa disponibilizada para download.',
        'detalhes_certidao' => [
            'numero_certidao' => 'TRF-999',
            'normalizado_data_hora_emissao' => '20/07/2026 10:00:00',
        ],
    ]]], 'sucesso');
    expect($emitida['certidao_trf']['status'])->not->toBe('Em andamento')
        ->and($emitida['certidao_trf']['certidao_codigo'])->toBe('TRF-999');

    // Sem `tribunais` e sem QUALQUER marca de emissão, a frase de pendência ainda vale.
    $pendente = $fonte->normalizar(['data' => [[
        'conseguiu_emitir' => false,
        'mensagem' => 'Solicitação aguardando processamento.',
        'detalhes_certidao' => [],
    ]]], 'sucesso');
    expect($pendente['certidao_trf']['status'])->toBe('Em andamento');
});

test('fonte pausada na origem some da tela e da selecao, sem cobrar', function () {
    $catalogo = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class);

    config()->set('consultas.fontes_pausadas', []);
    expect($catalogo->chavesDisponiveis())->toContain('falencias')->toContain('protestos');

    // Pausa protestos+falencias (estado prod 2026-07-22): saem do catálogo e do grupo passivo.
    config()->set('consultas.fontes_pausadas', ['falencias', 'protestos']);
    $catalogo = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class); // memo por instância
    $disp = $catalogo->chavesDisponiveis();
    expect($disp)->not->toContain('falencias')
        ->and($disp)->not->toContain('protestos')
        ->and($disp)->toContain('certidao_stj') // as demais seguem disponíveis
        ->and(array_key_exists('passivo', $catalogo->grupos()))->toBeFalse(); // grupo vazio some
});

test('pausa na origem vale pra QUALQUER fonte e barra a execucao, nao so a vitrine', function () {
    $registry = app(FonteRegistry::class);

    // O gate mora no registry (não numa base de provedor), então alcança até a fonte DERIVADA
    // `analise_fiscal`, que não herda de FonteInfoSimplesBase — era o buraco: o operador pausava
    // e ela continuava sendo vendida e executada.
    config()->set('consultas.fontes_pausadas', ['analise_fiscal']);
    expect($registry->pausada('analise_fiscal'))->toBeTrue()
        ->and(app(\App\Services\Advocacia\CatalogoFontesAvulsas::class)->chavesDisponiveis())
        ->not->toContain('analise_fiscal');

    // E a pausa barra a EXECUÇÃO, não só a tela: o job deriva as fontes de fontesDe(), então um
    // lote/plano criado ANTES da pausa não faz a chamada paga mesmo assim.
    config()->set('consultas.fontes_pausadas', ['protestos']);
    $chaves = array_map(fn ($f) => $f->chave(), $registry->fontesDe(['protestos', 'certidao_stj']));
    expect($chaves)->toContain('certidao_stj')->not->toContain('protestos')
        ->and($registry->cobre(['protestos']))->toBeFalse();

    // `get()` continua devolvendo a fonte pausada: o follow-up das 2 etapas precisa resolver
    // pedidos JÁ PAGOS e em voo (CertidaoPedidoService::verificar).
    expect($registry->get('protestos'))->not->toBeNull();
});

test('badge classifica "Em andamento" como pendente ambar (nao indeterminado)', function () {
    $badge = \App\Support\CertidaoBadge::classificar(['status' => \App\Support\CertidaoBadge::STATUS_EM_ANDAMENTO], true);
    expect($badge['label'])->toBe('Em andamento')
        ->and($badge['hex'])->toBe(\App\Support\CertidaoBadge::HEX_INDETERMINADO)
        ->and($badge['indeterminado'] ?? false)->toBeFalse(); // não dispara "Sem emissão online"

    // Match por IGUALDADE, não substring: um status externo contendo "andamento" NÃO vira pendente.
    $baixa = \App\Support\CertidaoBadge::classificar(['status' => 'Baixa de ofício em andamento'], true);
    expect($baixa['label'])->not->toBe('Em andamento')
        ->and($baixa['hex'])->not->toBe(\App\Support\CertidaoBadge::HEX_INDETERMINADO);
});

test('CertidaoRegistro NAO grava certidao em andamento (pedido pendente, nao emitida)', function () {
    $reg = app(\App\Services\Consultas\CertidaoRegistro::class);
    $out = $reg->registrar('certidao_trf', ['status' => 'Em andamento'], 1, 'participante', 1, '19131243000197', 1);
    expect($out)->toBeNull();
});

test('fonte de lista: nada consta vira Negativa, registros viram Positiva com resumo', function () {
    $fonte = new ProtestosFonte;

    $nada = $fonte->normalizar(['data' => [['cartorios' => []]]], 'sucesso');
    expect($nada['protestos']['status'])->toBe('Negativa')
        ->and($nada['protestos']['nada_consta'])->toBeTrue()
        ->and($nada['protestos']['total_registros'])->toBe(0);

    $com = $fonte->normalizar(['data' => [[
        'quantidade_titulos' => 3,
        'cartorios' => [
            ['cartorio' => '1º Tabelionato', 'cidade' => 'Campo Grande', 'uf' => 'MS', 'quantidade_titulos' => 3, 'endereco_ignorado' => 'x'],
        ],
    ]]], 'sucesso');
    expect($com['protestos']['status'])->toBe('Positiva')
        ->and($com['protestos']['total_titulos'])->toBe(3)
        ->and($com['protestos']['registros'][0])->toBe(['cartorio' => '1º Tabelionato', 'cidade' => 'Campo Grande', 'uf' => 'MS', 'quantidade_titulos' => 3])
        ->and($com['protestos']['mensagem'])->toContain('3 título(s)');
});

test('fonte de lista trata 612 nao_encontrado como nada consta (Negativa)', function () {
    // Visto no smoke prod (lote 260): CEIS/CNEP devolvem 612 p/ CNPJ sem sanção — isso é
    // resposta boa ("nada consta"), não neutro.
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CeisFonte;

    $bloco = $fonte->normalizar(['code' => 612, 'code_message' => 'sem dados'], 'nao_encontrado');
    expect($bloco['ceis']['status'])->toBe('Negativa')
        ->and($bloco['ceis']['nada_consta'])->toBeTrue()
        ->and($bloco['ceis']['total_registros'])->toBe(0);

    // Certidão comum mantém o comportamento neutro original no 612.
    $stj = (new CertidaoStjFonte)->normalizar(['code' => 612], 'nao_encontrado');
    expect($stj['certidao_stj']['status'])->toBe('NAO_ENCONTRADA');
});

test('presenter classifica bloco de lista Positiva como atencao no strip', function () {
    $user = \App\Models\User::factory()->create();
    $resultado = new \App\Models\ConsultaResultado([
        'resultado_dados' => [
            'protestos' => ['status' => 'Positiva', 'total_registros' => 2, 'mensagem' => 'Constam 2 registro(s).'],
            'certidao_stj' => ['status' => 'Negativa'],
        ],
    ]);

    $strip = app(\App\Services\Consultas\ResultadoDetalhePresenter::class)
        ->certidoes($resultado, ['protestos', 'certidao_stj']);

    $porChave = collect($strip)->keyBy('chave');
    expect($porChave['protestos']['estado'])->toBe('atencao')
        ->and($porChave['certidao_stj']['estado'])->toBe('regular')
        ->and($porChave['certidao_stj']['sigla'])->toBe('STJ');
});
