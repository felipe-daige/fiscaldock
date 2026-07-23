<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Dto\ResultadoFonte;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('é batchable (Bus::batch exige o trait Batchable)', function () {
    expect(in_array(Illuminate\Bus\Batchable::class, class_uses(ProcessarConsultaJob::class), true))->toBeTrue();
});

it('consulta cadastro, persiste e posta progresso', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake(['minhareceita.org/*' => Http::response([
        'razao_social' => 'EMPRESA X', 'descricao_situacao_cadastral' => 'ATIVA',
        'situacao_cadastral' => 2, 'uf' => 'MS', 'municipio' => 'CAMPO GRANDE',
        'cep' => '79005350', 'logradouro' => 'SALGADO FILHO', 'numero' => '2616', 'bairro' => 'JD AMERICA',
        'qsa' => [], 'cnaes_secundarios' => [], 'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
    ], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId,
        alvoTipo: 'participante',
        alvoId: $participanteId,
        userId: $userId,
        tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'dados_cadastrais', 'endereco'],
        alvo: ['cnpj' => '00000000000191'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['razao_social'])->toBe('EMPRESA X');
    expect($r->resultado_dados['situacao_cadastral'])->toBe('ATIVA');
    expect($r->status)->toBe('sucesso');

    $cache = Cache::get("progresso:{$userId}:tab-test");
    expect($cache)->not->toBeNull();
    expect($cache['total_etapas'])->toBe(2);
    // cadastro é grátis → nada a estornar
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('acumula estorno no cache quando uma fonte paga falha (fatal)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 601, 'code_message' => 'token inválido'], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Certidões Federais'],
    );

    // cnd_federal custoCreditos (config, default 2) deve ir pro estorno
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))
        ->toBe((int) config('consultas.fontes.cnd_federal', 2));

    // Alvo cuja ÚNICA fonte falha termina 'erro', não 'pendente'. gravarContextoAlvo semeia o
    // placeholder 'pendente' antes do loop; statusResolvido garante que a falha o sobrescreva
    // (senão o alvo ficava preso em 'pendente' num lote já CONCLUIDO).
    expect(ConsultaResultado::where('consulta_lote_id', $loteId)->first()->status)->toBe('erro');
});

it('CND Estadual em UF sem cobertura: não chama o provedor e marca INDISPONIVEL', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.cnd_estadual.ufs_cobertas', ['SP']);

    Http::fake(); // qualquer chamada falharia a asserção

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_estadual'], alvo: ['cnpj' => '19131243000197', 'uf' => 'AC'],
        etapas: ['Preparando consulta', 'Certidões Estaduais'],
    );

    Http::assertNothingSent();
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['cnd_estadual']['status'])->toBe('INDISPONIVEL');
    // não conta como estorno (não é falha)
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('usa a UF autoritativa do cadastro p/ liberar a CND Estadual (alvo sem UF)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.cnd_estadual.ufs_cobertas', ['SP']);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake([
        // cadastro: minhareceita devolve UF=SP (autoritativa)
        'minhareceita.org/*' => Http::response(['razao_social' => 'X', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2, 'uf' => 'SP', 'qsa' => [], 'cnaes_secundarios' => []], 200),
        // CND estadual: sucesso
        'api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa', 'uf' => 'SP']]], 200),
    ]);

    // alvo SEM uf — deve vir do cadastro
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'cnd_estadual'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Cadastrais', 'Estaduais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    // CND estadual foi consultada (não ficou INDISPONIVEL) porque a UF veio do cadastro
    expect($r->resultado_dados['cnd_estadual']['status'])->toBe('Negativa');
    Http::assertSent(fn ($req) => str_contains($req->url(), 'sefaz/sp/certidao-debitos'));
});

it('idempotência: fonte paga já persistida não é re-consultada (retry não re-cobra)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    // Simula uma tentativa anterior do job: cnd_federal já consultada e persistida.
    app(PersistenciaCnpj::class)->gravar($loteId, 'participante', $participanteId, new ResultadoFonte(
        'cnd_federal', ['cnd_federal' => ['status' => 'Negativa']], 'sucesso', 2, null,
    ));

    Http::fake(); // qualquer chamada ao InfoSimples falharia a asserção abaixo

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    Http::assertNothingSent(); // não re-chamou o provedor pago
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['cnd_federal']['status'])->toBe('Negativa'); // preservado
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('posta progresso 0..100 e mensagem por fonte (barra avança intra-grupo)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake([
        'minhareceita.org/*' => Http::response([
            'razao_social' => 'X', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2,
            'uf' => 'SP', 'qsa' => [], 'cnaes_secundarios' => [],
        ], 200),
        // todas as fontes InfoSimples (cnd_federal, cndt) respondem sucesso
        'api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200),
    ]);

    // Captura TODA escrita no cache de progresso (o key é sobrescrito, então o estado
    // final não revela a sequência — só os eventos KeyWritten revelam).
    $progressos = [];
    $mensagens = [];
    Event::listen(KeyWritten::class, function (KeyWritten $e) use (&$progressos, &$mensagens, $userId) {
        if ($e->key === "progresso:{$userId}:tab-test" && is_array($e->value)) {
            $progressos[] = $e->value['progresso'] ?? null;
            $mensagens[] = $e->value['mensagem'] ?? null;
        }
    });

    // 3 fontes: cadastro (gratis) + cnd_federal + cndt (federais, mesmo grupo de etapa)
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'cnd_federal', 'cndt'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Dados cadastrais', 'Certidões Federais'],
    );

    // O job emite progresso em TODA postagem (inicializacao + 1 por fonte).
    expect($progressos)->not->toBeEmpty();
    foreach ($progressos as $p) {
        expect($p)->not->toBeNull();
        expect($p)->toBeGreaterThanOrEqual(0);
        expect($p)->toBeLessThanOrEqual(100);
    }

    // Monotônico não-decrescente (a barra nunca volta atrás entre grupos/fontes).
    $ordenado = $progressos;
    sort($ordenado);
    expect($progressos)->toBe($ordenado);

    // Avança de verdade: pelo menos 3 valores distintos (inicio + ao menos 2 fontes).
    expect(count(array_unique($progressos)))->toBeGreaterThanOrEqual(3);

    // Feedback textual por fonte: ao menos uma mensagem nomeia a fonte sendo consultada.
    $temMensagemDeFonte = collect($mensagens)->contains(
        fn ($m) => is_string($m) && str_contains($m, 'Consultando')
    );
    expect($temMensagemDeFonte)->toBeTrue();
});

it('progresso multi-alvo é global e NÃO reseta entre empresas (empresa 2 não volta a 0%)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    $participante2Id = \Illuminate\Support\Facades\DB::table('participantes')->insertGetId([
        'user_id' => $userId, 'documento' => '11222333000181', 'razao_social' => 'PART 2',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake([
        'minhareceita.org/*' => Http::response([
            'razao_social' => 'X', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2,
            'uf' => 'SP', 'qsa' => [], 'cnaes_secundarios' => [],
        ], 200),
        'api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200),
    ]);

    $progressos = [];
    $ultimoPayload = null;
    Event::listen(KeyWritten::class, function (KeyWritten $e) use (&$progressos, &$ultimoPayload, $userId) {
        if ($e->key === "progresso:{$userId}:tab-multi" && is_array($e->value)) {
            $progressos[] = $e->value['progresso'] ?? null;
            $ultimoPayload = $e->value;
        }
    });

    $incluidas = ['situacao_cadastral', 'cnd_federal', 'cndt'];
    // Empresa 1 de 2, depois Empresa 2 de 2 — mesmo tabId (como no batch real).
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-multi',
        consultasIncluidas: $incluidas, alvo: ['cnpj' => '00000000000191'],
        etapas: ['Preparando', 'Cadastrais', 'Federais'], alvoIndice: 1, totalAlvos: 2,
    );
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participante2Id, userId: $userId, tabId: 'tab-multi',
        consultasIncluidas: $incluidas, alvo: ['cnpj' => '11222333000181'],
        etapas: ['Preparando', 'Cadastrais', 'Federais'], alvoIndice: 2, totalAlvos: 2,
    );

    // Monotônico global ao longo das DUAS empresas (a barra nunca volta atrás).
    $ordenado = $progressos;
    sort($ordenado);
    expect($progressos)->toBe($ordenado);
    // A empresa 2 entra na metade de cima (≥50%) — prova que NÃO resetou pra 0.
    expect(max($progressos))->toBeGreaterThanOrEqual(50);
    expect(min($progressos))->toBe(0);

    // Contexto textual + campos estruturados da fonte (consumidos pela checklist no front).
    expect($ultimoPayload['mensagem'])->toContain('Empresa 2 de 2');
    expect($ultimoPayload)->toHaveKey('fonte_nome');
    expect($ultimoPayload['fonte_total'])->toBe(3);
    expect($ultimoPayload['fonte_indice'])->toBeGreaterThanOrEqual(1);
});

it('processa escopo cliente gravando cliente_id', function () {
    [$loteId, , $userId] = montarLoteParticipante();
    $clienteId = \Illuminate\Support\Facades\DB::table('clientes')->where('user_id', $userId)->value('id');

    Http::fake(['minhareceita.org/*' => Http::response([
        'razao_social' => 'EMPRESA PROPRIA', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2,
        'qsa' => [], 'cnaes_secundarios' => [],
    ], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'cliente', alvoId: $clienteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'endereco'], alvo: ['cnpj' => '00000000000100'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->where('cliente_id', $clienteId)->firstOrFail();
    expect($r->participante_id)->toBeNull();
    expect($r->resultado_dados['razao_social'])->toBe('EMPRESA PROPRIA');
});

it('marca a fonte como falha na INTEGRAÇÃO quando o provedor não retorna resultado', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.retry.auto.max_tentativas', 0); // isola a marca (sem auto-retry)

    // code 609 = retry → normalizar devolve [] → chave ausente; marca origem 'integracao'
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 609, 'code_message' => 'temporário'], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['cnd_federal'] ?? null)->toBeNull();   // sem blob da fonte
    expect($r->resultado_dados['_fontes_erro']['cnd_federal'])->toMatchArray([
        'origem' => 'integracao', 'status' => 'retry', 'codigo' => 609, 'tentativas' => 0,
    ]);
});

it('auto-retry recupera fonte transitória (609→200): blob persistido, marca limpa e sem estorno', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.retry.auto.cooldown_segundos', 0);

    Http::fake(['api.infosimples.com/*' => Http::sequence()
        ->push(['code' => 609, 'code_message' => 'temporário'], 200)
        ->push(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    Http::assertSentCount(2);
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['cnd_federal']['status'])->toBe('Negativa');
    expect($r->resultado_dados['_fontes_erro'] ?? null)->toBeNull(); // sucesso limpou a marca
    // fonte entregue → nada a estornar
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('default = 2 tentativas: 615 origem fora persiste → recupera na 2ª (615→615→200)', function () {
    // Caso real do smoke (falências 615): origem oficial fora do ar. Com o default novo
    // (2 tentativas), o job re-bate 2× e pega a origem já no ar na 2ª — sem estornar nem
    // marcar erro. Cooldown zerado só p/ o teste não dormir (o backoff 15s/30s é do prod).
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.retry.auto.cooldown_segundos', 0);

    Http::fake(['api.infosimples.com/*' => Http::sequence()
        ->push(['code' => 615, 'code_message' => 'origem indisponível'], 200)
        ->push(['code' => 615, 'code_message' => 'origem indisponível'], 200)
        ->push(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    Http::assertSentCount(3); // original + 2 auto-retries (default max_tentativas = 2)
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['cnd_federal']['status'])->toBe('Negativa')
        ->and($r->resultado_dados['_fontes_erro'] ?? null)->toBeNull();
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('auto-retry re-falha (609→609): 1 marca com tentativas=1 e estorno contado UMA vez', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.retry.auto.cooldown_segundos', 0);
    config()->set('consultas.retry.auto.max_tentativas', 1); // isola a marca/estorno do default

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 609, 'code_message' => 'temporário'], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    Http::assertSentCount(2); // original + 1 auto-retry (max_tentativas fixado em 1 acima)
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['_fontes_erro']['cnd_federal'])->toMatchArray([
        'origem' => 'integracao', 'status' => 'retry', 'codigo' => 609, 'tentativas' => 1,
    ]);
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))
        ->toBe((int) config('consultas.fontes.cnd_federal', 2)); // não conta 2×
});

it('auto-retry NÃO retenta erro_participante (620, cobrado e determinístico)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.retry.auto.cooldown_segundos', 0);

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 620, 'code_message' => 'dados do CNPJ recusados'], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando', 'Federais'],
    );

    Http::assertSentCount(1); // sem re-tentativa
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['_fontes_erro']['cnd_federal']['tentativas'])->toBe(0);
    // erro_participante não é estornável (a InfoSimples cobra a chamada)
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))->toBe(0);
});

it('dedup intra-lote: chamada idêntica (CND Federal de 2 filiais da mesma matriz) sai 1× do provedor', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    $participante2Id = \Illuminate\Support\Facades\DB::table('participantes')->insertGetId([
        'user_id' => $userId, 'documento' => '19131243000278', 'razao_social' => 'FILIAL 2',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200)]);

    // Duas filiais da MESMA raiz: a fonte normaliza ambas pro CNPJ da matriz → params idênticos.
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000278'],
        etapas: ['Preparando', 'Federais'], alvoIndice: 1, totalAlvos: 2,
    );
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participante2Id, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000359'],
        etapas: ['Preparando', 'Federais'], alvoIndice: 2, totalAlvos: 2,
    );

    Http::assertSentCount(1); // 2ª filial reutilizou a resposta cacheada do lote
    $r1 = ConsultaResultado::where('consulta_lote_id', $loteId)->where('participante_id', $participanteId)->firstOrFail();
    $r2 = ConsultaResultado::where('consulta_lote_id', $loteId)->where('participante_id', $participante2Id)->firstOrFail();
    expect($r1->resultado_dados['cnd_federal']['status'])->toBe('Negativa');
    expect($r2->resultado_dados['cnd_federal']['status'])->toBe('Negativa');
});

it('marcarErroFonte registra a origem e o sucesso posterior limpa a marca', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    $p = app(PersistenciaCnpj::class);

    $p->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'interno');
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['_fontes_erro']['cnd_federal'])->toMatchArray([
        'origem' => 'interno', 'status' => null,
    ]);

    // re-consulta bem-sucedida da mesma fonte limpa a marca de erro
    $p->gravar($loteId, 'participante', $participanteId, new ResultadoFonte(
        'cnd_federal', ['cnd_federal' => ['status' => 'Negativa']], 'sucesso', 2, null,
    ));
    $r->refresh();
    expect($r->resultado_dados['_fontes_erro'] ?? null)->toBeNull();
    expect($r->resultado_dados['cnd_federal']['status'])->toBe('Negativa');
});

it('retry de 1 fonte: progresso termina em 100 (barra não fica presa em 0)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200)]);

    // Cenário da reconsulta: somenteFontes com UMA fonte. Antes do fix, a única emissão de
    // progresso era pctGlobal(0,1)=0 — a barra ficava em 0% durante toda a reconsulta.
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-retry',
        consultasIncluidas: ['situacao_cadastral', 'cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Certidões Federais'],
        somenteFontes: ['cnd_federal'],
    );

    $cache = Cache::get("progresso:{$userId}:tab-retry");
    expect($cache)->not->toBeNull();
    expect((int) $cache['progresso'])->toBe(100);
});

it('progresso avança após cada fonte e é monotônico (consulta cheia)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake([
        'minhareceita.org/*' => Http::response(['razao_social' => 'X', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2, 'uf' => 'SP', 'qsa' => [], 'cnaes_secundarios' => []], 200),
        'api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200),
    ]);

    $valores = [];
    Event::listen(KeyWritten::class, function (KeyWritten $e) use (&$valores, $userId) {
        if ($e->key === "progresso:{$userId}:tab-mono" && isset($e->value['progresso'])) {
            $valores[] = (int) $e->value['progresso'];
        }
    });

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-mono',
        consultasIncluidas: ['situacao_cadastral', 'cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Cadastrais', 'Certidões Federais'],
    );

    expect($valores)->not->toBeEmpty();
    expect(end($valores))->toBe(100);
    $ordenado = $valores;
    sort($ordenado);
    expect($valores)->toBe($ordenado); // nunca retrocede (UI não tem clamp)
});
