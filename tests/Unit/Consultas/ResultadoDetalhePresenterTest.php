<?php

use App\Models\ConsultaResultado;
use App\Services\Consultas\ResultadoDetalhePresenter;

function resultadoComDados(array $dados): ConsultaResultado
{
    $r = new ConsultaResultado;
    $r->status = ConsultaResultado::STATUS_SUCESSO;
    $r->resultado_dados = $dados;

    return $r;
}

it('prefere a rota local do comprovante e mantém a URL original como fallback', function () {
    $comArquivo = resultadoComDados([
        'cnd_federal' => [
            'status' => 'Negativa',
            'comprovante' => 'https://origem.example/cnd.pdf',
            'comprovante_arquivo' => 'comprovantes/1/2026/07/cnd.pdf',
        ],
    ]);
    $comArquivo->id = 123;
    $semArquivo = resultadoComDados([
        'cnd_federal' => [
            'status' => 'Negativa',
            'comprovante' => 'https://origem.example/cnd.pdf',
        ],
    ]);

    $presenter = new ResultadoDetalhePresenter;
    expect(bloco($presenter->blocos($comArquivo), 'cnd_federal')['comprovante_url'])
        ->toBe(route('app.consulta.comprovante', [123, 'cnd_federal']))
        ->and(bloco($presenter->blocos($semArquivo), 'cnd_federal')['comprovante_url'])
        ->toBe('https://origem.example/cnd.pdf');
});

function bloco(array $blocos, string $chave): ?array
{
    foreach ($blocos as $b) {
        if (($b['chave'] ?? null) === $chave) {
            return $b;
        }
    }

    return null;
}

it('lida com _fontes_erro no shape objeto (retry) sem TypeError', function () {
    $resultado = resultadoComDados([
        'razao_social' => 'ACME LTDA',
        'situacao_cadastral' => 'ATIVA',
        '_fontes_erro' => [
            'cnd_federal' => ['codigo' => 615, 'origem' => 'integracao', 'status' => 'retry', 'tentativas' => 0],
            'sintegra' => ['codigo' => 609, 'origem' => 'interno', 'status' => 'erro', 'tentativas' => 0],
        ],
    ]);
    $presenter = new ResultadoDetalhePresenter;

    // blocos(): cnd_federal pedido mas ausente → card de falha; status retry/615 = órgão fora do ar
    $cnd = bloco($presenter->blocos($resultado, ['cnd_federal', 'sintegra']), 'cnd_federal');
    expect($cnd)->not->toBeNull();
    expect($cnd['badge']['label'])->toBe('Órgão fora do ar');

    // certidoes() strip: sintegra origem interno → erro interno
    $strip = collect($presenter->certidoes($resultado, ['cnd_federal', 'sintegra']));
    expect($strip->firstWhere('chave', 'sintegra')['label'])->toBe('Erro interno');
});

it('monta bloco de dados cadastrais com itens e listas (CNAEs/QSA)', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'razao_social' => 'ACME LTDA',
        'nome_fantasia' => 'Acme',
        'situacao_cadastral' => 'ATIVA',
        'motivo_situacao_cadastral' => 'SEM MOTIVO',
        'natureza_juridica' => 'LTDA',
        'porte' => 'DEMAIS',
        'capital_social' => 100000,
        'data_inicio_atividade' => '2010-01-01',
        'regime_tributario' => 'Lucro Presumido',
        'endereco' => ['logradouro' => 'Rua X', 'numero' => '10', 'municipio' => 'Dourados', 'uf' => 'MS'],
        'cnaes' => [['codigo' => '6201-5/01', 'descricao' => 'Software', 'principal' => true]],
        'qsa' => [['nome' => 'João', 'qualificacao' => 'Sócio', 'data_entrada' => '2010-01-01']],
    ]));

    $cad = bloco($blocos, 'cadastro');
    expect($cad)->not->toBeNull();
    expect($cad['titulo'])->toBe('Dados cadastrais');

    $labels = array_column($cad['itens'], 'label');
    expect($labels)->toContain('Capital social')->toContain('Endereço')->toContain('Regime tributário');

    $tituloListas = array_column($cad['listas'], 'titulo');
    expect($tituloListas)->toContain('CNAEs')->toContain('Quadro societário (QSA)');
});

it('monta blocos ricos das fontes PF sem tratar cadastro como certidao generica', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'cadastro_pf' => [
            'status' => 'REGULAR',
            'situacao_cadastral' => 'REGULAR',
            'cpf' => '52998224725',
            'nome' => 'MARIA DA SILVA',
            'data_nascimento' => '10/05/1980',
        ],
        'quitacao_eleitoral' => [
            'status' => 'Negativa',
            'nome' => 'MARIA DA SILVA',
            'titulo_eleitoral' => '123456789012',
            'biometria_coletada' => true,
            'certidao_codigo' => 'TSE-1',
            'domicilio_eleitoral' => ['municipio' => 'Dourados', 'uf' => 'MS', 'zona' => '18'],
        ],
        'mandado_prisao' => [
            'status' => 'Positiva',
            'total_registros' => 1,
            'registros' => [[
                'mandado' => 'M-1',
                'processo' => 'P-1',
                'situacao' => 'Aguardando cumprimento',
                'tribunal' => 'TJMS',
            ]],
        ],
    ]));

    $cadastro = bloco($blocos, 'cadastro_pf');
    $quitacao = bloco($blocos, 'quitacao_eleitoral');
    $mandado = bloco($blocos, 'mandado_prisao');

    expect(collect($cadastro['itens'])->keyBy('label')->get('CPF')['valor'])->toBe('529.982.247-25')
        ->and(collect($quitacao['itens'])->keyBy('label')->get('Domicílio eleitoral')['valor'])
        ->toContain('Dourados')
        ->and($mandado['listas'][0]['linhas'][0])->toContain('Mandado M-1')
        ->and($mandado['listas'][0]['linhas'][0])->toContain('Processo P-1');
});

it('monta blocos das novas fontes públicas com registros e paginação', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'tcu_cni_inidoneo' => [
            'status' => 'Positiva',
            'certidao_codigo' => 'CNI-1',
            'total_processos' => 1,
            'processos' => [[
                'processo' => '1111111-11.1111.1.11.1111',
                'acordao' => '111/2026',
            ]],
        ],
        'inpi_marcas_titular' => [
            'status' => 'Positiva',
            'total_registros' => 8,
            'pagina_atual' => 1,
            'total_paginas' => 3,
            'tem_mais_paginas' => true,
            'registros' => [[
                'numero' => '123',
                'marca' => 'FiscalDock',
                'situacao' => 'Registro em vigor',
            ]],
        ],
        'bcb_valores_receber' => [
            'status' => 'Positiva',
            'possui_valores_receber' => true,
            'mensagem' => 'Há valores a receber.',
        ],
    ]));

    $tcu = bloco($blocos, 'tcu_cni_inidoneo');
    $inpi = bloco($blocos, 'inpi_marcas_titular');
    $bcb = bloco($blocos, 'bcb_valores_receber');

    expect($tcu['listas'][0]['linhas'][0])->toContain('Acórdão: 111/2026')
        ->and($inpi['listas'][0]['linhas'][0])->toContain('Marca: FiscalDock')
        ->and($inpi['nota'])->toContain('páginas adicionais')
        ->and(collect($bcb['itens'])->keyBy('label')->get('Possui valores a receber')['valor'])
        ->toBe('Sim');
});

it('monta blocos de certidões com código, validade e mensagem oficial', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'cnd_federal' => [
            'status' => 'Positiva com efeitos de negativa',
            'mensagem' => 'CERTIDÃO POSITIVA COM EFEITOS DE NEGATIVA',
            'certidao_codigo' => 'A3E5.6BE2',
            'emissao_data' => '19/05/2026',
            'data_validade' => '15/11/2026',
            'debitos_rfb' => true,
            'debitos_pgfn' => false,
            'conseguiu_emitir' => true,
        ],
    ]));

    $b = bloco($blocos, 'cnd_federal');
    expect($b)->not->toBeNull();
    expect($b['badge']['label'])->toBe('Regular'); // Positiva com efeitos = regular
    expect($b['mensagem'])->toContain('CERTIDÃO POSITIVA');

    $itens = collect($b['itens'])->keyBy('label');
    expect($itens->get('Certidão nº')['valor'])->toBe('A3E5.6BE2');
    expect($itens->get('Validade')['valor'])->toBe('15/11/2026');
});

it('inclui CND Estadual e SINTEGRA que hoje não aparecem na tabela', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'cnd_estadual' => ['status' => 'Negativa', 'certidao_codigo' => '573628/2026', 'data_validade' => '04/08/2026'],
        'sintegra' => ['situacao' => 'HABILITADO', 'inscricao_estadual' => '28.368.441-0', 'atividade_economica' => 'C3314702'],
    ]));

    $est = bloco($blocos, 'cnd_estadual');
    expect($est)->not->toBeNull();
    expect($est['badge']['label'])->toBe('Regular');

    $sin = bloco($blocos, 'sintegra');
    expect($sin)->not->toBeNull();
    $itens = collect($sin['itens'])->keyBy('label');
    expect($itens->get('Inscrição estadual')['valor'])->toBe('28.368.441-0');
});

it('compõe linha-resumo para FGTS e SINTEGRA, que não trazem frase do provedor', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'endereco' => ['uf' => 'MS'],
        'crf_fgts' => ['status' => 'REGULAR', 'data_validade' => '16/07/2026'],
        'sintegra' => ['situacao' => 'HABILITADO', 'inscricao_estadual' => '28.337.553-1', 'uf' => null],
    ]));

    $fgts = bloco($blocos, 'crf_fgts');
    expect($fgts['mensagem'])->toContain('regular perante o FGTS');
    expect($fgts['mensagem'])->toContain('16/07/2026');

    $sin = bloco($blocos, 'sintegra');
    expect($sin['mensagem'])->toBe('Contribuinte HABILITADO no cadastro SINTEGRA-MS (IE 28.337.553-1).');
});

it('FGTS irregular ganha linha-resumo de pendência', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'crf_fgts' => ['status' => 'IRREGULAR'],
    ]));

    $fgts = bloco($blocos, 'crf_fgts');
    expect($fgts['mensagem'])->toContain('sem Certificado de Regularidade');
});

it('completa a UF da CND Estadual a partir do endereço quando a resposta vem sem UF', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'endereco' => ['uf' => 'MS'],
        'cnd_estadual' => ['uf' => null, 'status' => 'Negativa', 'certidao_codigo' => '573628/2026'],
    ]));

    $est = bloco($blocos, 'cnd_estadual');
    expect($est['titulo'])->toBe('CND Estadual (SEFAZ-MS)');
    $itens = collect($est['itens'])->keyBy('label');
    expect($itens->get('UF')['valor'])->toBe('MS');
});

it('mostra CND Municipal INDISPONIVEL com mensagem em vez de sumir', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'cnd_municipal' => ['status' => 'INDISPONIVEL', 'mensagem' => 'CND Municipal não disponível para DOURADOS/MS.'],
    ]));

    $b = bloco($blocos, 'cnd_municipal');
    expect($b)->not->toBeNull();
    expect($b['badge']['label'])->toBe('Indisponível');
    expect($b['mensagem'])->toContain('DOURADOS/MS');
});

it('não cria bloco para fonte ausente', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'situacao_cadastral' => 'ATIVA',
    ]));

    expect(bloco($blocos, 'cndt'))->toBeNull();
    expect(bloco($blocos, 'sintegra'))->toBeNull();
});

it('gera resumo textual com situação cadastral e regularidade quando tudo OK', function () {
    $texto = (new ResultadoDetalhePresenter)->resumoTextual(resultadoComDados([
        'razao_social' => 'ACME LTDA',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Simples Nacional',
        'cnd_federal' => ['status' => 'Negativa'],
        'cndt' => ['status' => 'Negativa'],
    ]));

    expect($texto)->toContain('ATIVA');
    expect(mb_strtolower($texto))->toContain('regular');
});

it('gera resumo textual sinalizando pendências e inatividade', function () {
    $texto = (new ResultadoDetalhePresenter)->resumoTextual(resultadoComDados([
        'situacao_cadastral' => 'BAIXADA',
        'cnd_federal' => ['status' => 'Positiva'],
    ]));

    $low = mb_strtolower($texto);
    expect($low)->toContain('baixada');
    expect($low)->toContain('pend'); // pendência/pendências
});

it('agrega a análise do lote por fonte e por CNPJ', function () {
    $presenter = new ResultadoDetalhePresenter;

    $rows = [
        ['detalhe_blocos' => $presenter->blocos(resultadoComDados([
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
            'cndt' => ['status' => 'Negativa'],
        ]))],
        ['detalhe_blocos' => $presenter->blocos(resultadoComDados([
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Positiva'],
            'cndt' => ['status' => 'Negativa'],
        ]))],
    ];

    $analise = $presenter->analiseLote($rows);

    expect($analise['total'])->toBe(2);

    $cndFederal = collect($analise['por_fonte'])->firstWhere('chave', 'cnd_federal');
    expect($cndFederal['regular'])->toBe(1);
    expect($cndFederal['atencao'])->toBe(1);

    expect($analise['cnpjs']['regular'])->toBe(1);
    expect($analise['cnpjs']['pendencia'])->toBe(1);
    expect($analise['texto'])->toBeString()->not->toBe('');
});

it('ordena cadastro primeiro e mantém ordem canônica das fontes', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'sintegra' => ['situacao' => 'Habilitado'],
        'cnd_federal' => ['status' => 'Negativa'],
        'razao_social' => 'ACME',
        'situacao_cadastral' => 'ATIVA',
    ]));

    $chaves = array_column($blocos, 'chave');
    expect($chaves[0])->toBe('cadastro');
    expect(array_search('cnd_federal', $chaves, true))->toBeLessThan(array_search('sintegra', $chaves, true));
});

// ── Strip de certidões (coluna agrupada) + estado "Falhou" ───────────────────

it('certidoes() classifica fontes presentes com sigla e badge', function () {
    $certs = (new ResultadoDetalhePresenter)->certidoes(resultadoComDados([
        'cnd_federal' => ['status' => 'Negativa'],
        'crf_fgts' => ['status' => 'Regular'],
        'cndt' => ['status' => 'Positiva'],
    ]), ['cnd_federal', 'crf_fgts', 'cndt']);

    $fed = collect($certs)->firstWhere('chave', 'cnd_federal');
    expect($fed['sigla'])->toBe('FED');
    expect($fed['hex'])->toBe(\App\Support\CertidaoBadge::HEX_REGULAR);
    expect($fed['estado'])->toBe('regular');

    $cndt = collect($certs)->firstWhere('chave', 'cndt');
    expect($cndt['hex'])->toBe(\App\Support\CertidaoBadge::HEX_IRREGULAR);
});

it('certidoes() separa "não consultada" (sem marcador) de erro do provedor (com marcador)', function () {
    // Semântica da migração escada→à la carte (4664d05): ESPERADA e ausente SEM `_fontes_erro`
    // não é falha — é consulta que nunca foi pedida. A tela de seleção passa "todas as consultas
    // possíveis" como esperadas, então tratar ausência como erro pintaria de vermelho tudo que o
    // usuário simplesmente não comprou.
    $naoConsultada = (new ResultadoDetalhePresenter)->certidoes(resultadoComDados([
        'crf_fgts' => ['status' => 'Regular'],
    ]), ['cnd_federal', 'crf_fgts']);

    $fed = collect($naoConsultada)->firstWhere('chave', 'cnd_federal');
    expect($fed)->not->toBeNull();
    expect($fed['estado'])->toBe('neutro');
    expect($fed['label'])->toBe('Não consultada');

    // COM marcador de erro (a fonte foi pedida e o provedor falhou) segue erro de integração.
    $comErro = (new ResultadoDetalhePresenter)->certidoes(resultadoComDados([
        'crf_fgts' => ['status' => 'Regular'],
        '_fontes_erro' => ['cnd_federal' => 'integracao'],
    ]), ['cnd_federal', 'crf_fgts']);

    $fed = collect($comErro)->firstWhere('chave', 'cnd_federal');
    expect($fed['estado'])->toBe('erro_integracao');
    expect($fed['label'])->toBe('Erro com o site de consultas do provedor');
    expect($fed['hex'])->toBe(\App\Support\CertidaoBadge::HEX_FALHOU);
    expect($fed['descricao'])->toBeString()->not->toBe('');
});

it('certidoes() separa "Erro interno" quando o marcador _fontes_erro aponta interno', function () {
    $certs = (new ResultadoDetalhePresenter)->certidoes(resultadoComDados([
        'crf_fgts' => ['status' => 'Regular'],
        '_fontes_erro' => ['cnd_federal' => 'interno'],
    ]), ['cnd_federal', 'crf_fgts']);

    $fed = collect($certs)->firstWhere('chave', 'cnd_federal');
    expect($fed['estado'])->toBe('erro_interno');
    expect($fed['label'])->toBe('Erro interno');
    expect($fed['hex'])->toBe(\App\Support\CertidaoBadge::HEX_ERRO_INTERNO);
});

it('certidoes() omite fonte fora do plano e ausente', function () {
    $certs = (new ResultadoDetalhePresenter)->certidoes(resultadoComDados([
        'cnd_federal' => ['status' => 'Negativa'],
    ]), ['cnd_federal']); // plano só inclui federal

    $chaves = array_column($certs, 'chave');
    expect($chaves)->toContain('cnd_federal');
    expect($chaves)->not->toContain('cnd_estadual');
    expect($chaves)->not->toContain('sintegra');
});

it('blocos() injeta placeholder "Falhou" só com marcador de erro; sem ele é "não consultada"', function () {
    $presenter = new ResultadoDetalhePresenter;
    $dados = ['situacao_cadastral' => 'ATIVA', 'crf_fgts' => ['status' => 'Regular']];

    // Esperada + ausente + COM `_fontes_erro` → placeholder de falha.
    $comErro = $presenter->blocos(
        resultadoComDados($dados + ['_fontes_erro' => ['cnd_federal' => 'integracao']]),
        ['cnd_federal', 'crf_fgts']
    );
    $fed = bloco($comErro, 'cnd_federal');
    expect($fed)->not->toBeNull();
    expect($fed['badge']['label'])->toBe('Erro com o site de consultas do provedor');

    // Sem marcador → card neutro "não consultada" (a fonte nunca chegou a ser pedida).
    $semErro = $presenter->blocos(resultadoComDados($dados), ['cnd_federal', 'crf_fgts']);
    expect(bloco($semErro, 'cnd_federal'))->not->toBeNull();

    // back-compat: sem esperadas não inventa bloco nenhum
    $semEsperadas = $presenter->blocos(resultadoComDados($dados));
    expect(bloco($semEsperadas, 'cnd_federal'))->toBeNull();
});

it('analiseLote conta fonte que falhou no bucket falha (distinto de não consultado)', function () {
    $presenter = new ResultadoDetalhePresenter;
    $rows = [
        ['detalhe_blocos' => $presenter->blocos(resultadoComDados([
            'situacao_cadastral' => 'ATIVA',
            'crf_fgts' => ['status' => 'Regular'],
            // O marcador é o que distingue FALHA de "nunca consultada" — sem ele o bucket certo
            // passa a ser `neutro` (semântica da migração à la carte).
            '_fontes_erro' => ['cnd_federal' => 'integracao'],
        ]), ['cnd_federal', 'crf_fgts'])],
    ];

    $analise = $presenter->analiseLote($rows);
    $fed = collect($analise['por_fonte'])->firstWhere('chave', 'cnd_federal');
    expect($fed)->not->toBeNull();
    expect($fed['falha'])->toBe(1);
    expect($fed['neutro'])->toBe(0);
    expect($analise['falhas'])->toBe(1);
});

// ── Certidão "sem emissão" (fonte recusou emitir online) ─────────────────────

it('certidão estadual sem emissão vira Indeterminada com nota didática e situação honesta', function () {
    // Caso real (SEFAZ-MS): status "Positiva" derivado de conseguiu_emitir=false, sem nº/data.
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'endereco' => ['uf' => 'MS'],
        'cnd_estadual' => [
            'uf' => null, 'status' => 'Positiva', 'conseguiu_emitir' => false,
            'certidao_codigo' => null, 'emissao_data' => null,
            'mensagem' => 'Não foi possível a emissão da sua Certidão Negativa.',
        ],
    ]));

    $est = bloco($blocos, 'cnd_estadual');
    expect($est['badge']['label'])->toBe('Indeterminada');
    expect($est['nota'])->toContain('SEFAZ-MS');
    expect($est['nota'])->toContain('não comprova irregularidade');
    $situacao = collect($est['itens'])->firstWhere('label', 'Situação informada');
    expect($situacao['valor'])->toBe('Sem emissão online');
});

it('certidão Positiva EMITIDA segue Irregular, sem nota de sem-emissão', function () {
    $blocos = (new ResultadoDetalhePresenter)->blocos(resultadoComDados([
        'cnd_estadual' => [
            'status' => 'Positiva', 'conseguiu_emitir' => false,
            'certidao_codigo' => '2026/123', 'emissao_data' => '02/07/2026',
        ],
    ]));

    $est = bloco($blocos, 'cnd_estadual');
    expect($est['badge']['label'])->toBe('Irregular');
    expect($est['nota'])->toBeNull();
    $situacao = collect($est['itens'])->firstWhere('label', 'Situação informada');
    expect($situacao['valor'])->toBe('Positiva');
});
