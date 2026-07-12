<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\Consultas\RetryConsultaService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

/** Grava o mapa _fontes_erro direto num ConsultaResultado (participante). */
function gravarFontesErro(int $loteId, int $participanteId, array $erros): void
{
    $linha = ConsultaResultado::firstOrNew([
        'consulta_lote_id' => $loteId,
        'participante_id' => $participanteId,
    ]);
    $dados = $linha->resultado_dados ?? [];
    $dados['_fontes_erro'] = $erros;
    $linha->resultado_dados = $dados;
    $linha->status = $linha->status ?: 'erro';
    $linha->save();
}

function custoFonte(string $chave): int
{
    return app(FonteRegistry::class)->get($chave)->custoCreditos();
}

// ---------------------------------------------------------------------------
// Task 1 — _fontes_erro enriquecido (objeto + tentativas + retrocompat)
// ---------------------------------------------------------------------------

it('grava _fontes_erro como objeto com status/codigo/tentativas', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    $svc = app(PersistenciaCnpj::class);

    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);

    $row = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($row->resultado_dados['_fontes_erro']['cnd_federal'])->toMatchArray([
        'origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0,
    ]);
});

it('preserva tentativas numa re-falha da mesma fonte', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    $svc = app(PersistenciaCnpj::class);

    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);
    $svc->incrementarTentativaFonte($loteId, 'participante', $participanteId, 'cnd_federal');
    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);

    $row = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($row->resultado_dados['_fontes_erro']['cnd_federal']['tentativas'])->toBe(1);
});

it('normaliza entrada string legada como retry/tentativas-0', function () {
    $svc = app(PersistenciaCnpj::class);

    expect($svc->normalizarFontesErro(['cnd_federal' => 'integracao']))->toMatchArray([
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => null, 'tentativas' => 0],
    ]);
    expect($svc->normalizarFontesErro(['x' => 'interno'])['x']['status'])->toBeNull();
});

// ---------------------------------------------------------------------------
// Task 2 — RetryConsultaService: pendentesRetry + precificar
// ---------------------------------------------------------------------------

it('lista fontes retry como elegíveis independente de tentativas (retry ilimitado)', function () {
    config()->set('consultas.retry.desconto_pct', 50);
    [$loteId, $participanteId] = montarLoteParticipante();

    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
        'crf_fgts' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    // crf_fgts já reconsultada 1× (tentativas 1) segue ELEGÍVEL (sem cap). Só fatal é inelegível.
    expect(collect($out['elegiveis'])->pluck('fonte')->sort()->values()->all())->toBe(['cnd_federal', 'crf_fgts']);
    expect(collect($out['inelegiveis'])->pluck('fonte')->all())->toBe(['cndt']);
});

it('precifica a reconsulta como preço do plano com desconto por CNPJ afetado (não por fonte)', function () {
    config()->set('consultas.retry.desconto_pct', 50);
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    // 2 fontes do MESMO CNPJ falharam → 1 só CNPJ afetado, cobra 1× preço do plano com desconto.
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
        'sintegra' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 605, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry($lote->fresh());
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);

    expect($out['alvos'])->toHaveCount(1);
    expect($out['alvos'][0]['preco_creditos'])->toBe($precoPlano);
    expect($out['alvos'][0]['cnpj'])->toBe('19131243000197');
    expect(collect($out['alvos'][0]['fontes'])->count())->toBe(2); // ambas listadas (info)
    expect($out['total_preco_creditos'])->toBe($precoPlano); // 1 CNPJ × preço do plano c/ desconto
});

it('expõe o desconto EFETIVO (preço cobrado vs plano), não o nominal — afetado por arredondamento', function () {
    config()->set('consultas.retry.desconto_pct', 50);

    // compliance 25 créditos → ceil(12,5)=13 → desconto efetivo 48% (não 50).
    [$loteC, $pc] = montarLoteComPlano('compliance');
    gravarFontesErro($loteC->id, $pc->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);
    expect(app(RetryConsultaService::class)->pendentesRetry($loteC->fresh())['desconto_pct_efetivo'])->toBe(48);

    // licitação 20 créditos → ceil(10)=10 → desconto efetivo 50% (bate o nominal).
    [$loteL, $pl] = montarLoteComPlano('licitacao');
    gravarFontesErro($loteL->id, $pl->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);
    expect(app(RetryConsultaService::class)->pendentesRetry($loteL->fresh())['desconto_pct_efetivo'])->toBe(50);
});

it('marca o motivo dos inelegíveis (fatal); retry segue elegível mesmo já tentado', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
        'crf_fgts' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect(collect($out['inelegiveis'])->pluck('motivo', 'fonte')->all())->toBe(['cndt' => 'fatal']);
    expect(collect($out['elegiveis'])->pluck('fonte')->all())->toBe(['crf_fgts']); // já tentado, segue elegível
});

it('precifica somando ceil por fonte', function () {
    config()->set('consultas.retry.desconto_pct', 50);
    $r = app(RetryConsultaService::class)->precificar([
        ['custo_creditos' => 5], ['custo_creditos' => 3], // ceil(2.5)+ceil(1.5)=3+2=5
    ]);
    expect($r['creditos'])->toBe(5);
});

// ---------------------------------------------------------------------------
// Task 3 — Job somenteFontes + executar + FecharRetryService (settlement)
// ---------------------------------------------------------------------------

use App\Jobs\ProcessarConsultaJob;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\Consultas\FecharRetryService;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

function montarLoteComPlano(string $codigo = 'licitacao'): array
{
    $user = User::factory()->create();
    $participante = Participante::create([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART', 'uf' => 'SP',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo($codigo)->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 10,
        'tab_id' => 'tab-test',
    ]);

    return [$lote, $participante, $user];
}

it('executar (sem seleção) debita plano×50% por CNPJ e reconsulta só as fontes com erro', function () {
    Bus::fake();
    config()->set('consultas.retry.desconto_pct', 50);
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    app(SaldoService::class)->add($user, 100);
    // cnd_federal falhou (elegível); cndt já deu certo (não está em _fontes_erro).
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);

    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    // Backend-autoritativo: sem argumento de seleção.
    app(RetryConsultaService::class)->executar($lote->fresh());

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes - $precoPlano);

    // reconsulta SÓ as fontes com erro (não o plano inteiro).
    Bus::assertBatched(fn ($batch) => collect($batch->jobs)->contains(
        fn ($job) => $job instanceof ProcessarConsultaJob && $job->somenteFontes === ['cnd_federal']
    ));

    $row = ConsultaResultado::where('consulta_lote_id', $lote->id)->first();
    $erros = app(PersistenciaCnpj::class)->normalizarFontesErro($row->resultado_dados['_fontes_erro']);
    expect($erros['cnd_federal']['tentativas'])->toBe(1); // trava 1x antes do dispatch

    // envelope de cobrança do alvo = preço do plano (não soma de fontes).
    expect((int) Cache::get("consulta_retry_charge:{$lote->id}:participante:{$p->id}"))->toBe($precoPlano);
});

it('executar não cobra nem reconsulta CNPJ que só tem fonte inelegível (fatal)', function () {
    Bus::fake();
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
    ]);

    expect(fn () => app(RetryConsultaService::class)->executar($lote->fresh()))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class); // 422 nenhum elegível
});

it('settlement estorna o valor cheio do CNPJ quando NENHUMA fonte reconsultada teve sucesso', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);

    // envelope per-alvo = preço do plano; ambas as fontes re-falharam (ainda em _fontes_erro).
    Cache::put("consulta_retry_charge:{$lote->id}:participante:{$p->id}", $precoPlano, 86400);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1],
        'sintegra' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 605, 'tentativas' => 1],
    ]);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'cnd_federal'],
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'sintegra'],
    ]);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes + $precoPlano);
});

it('settlement estorna a soma de TODOS os CNPJs que re-falharam (multi-alvo, espelha lote 215)', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5); // compliance 25 → 13
    // 2 participantes + 1 cliente, TODOS zero-sucesso (cnd_federal ainda em _fontes_erro).
    $p2 = App\Models\Participante::create(['user_id' => $user->id, 'documento' => '08906558000142', 'razao_social' => 'P2', 'uf' => 'MS']);
    $cli = App\Models\Cliente::create(['user_id' => $user->id, 'documento' => '97551165000193', 'razao_social' => 'C1']);

    foreach ([[$p->id, 'participante'], [$p2->id, 'participante'], [$cli->id, 'cliente']] as [$id, $tipo]) {
        $col = $tipo === 'cliente' ? 'cliente_id' : 'participante_id';
        App\Models\ConsultaResultado::create([
            'consulta_lote_id' => $lote->id, $col => $id, 'status' => 'erro',
            'resultado_dados' => ['_fontes_erro' => ['cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1]]],
        ]);
        Cache::put("consulta_retry_charge:{$lote->id}:{$tipo}:{$id}", $precoPlano, 86400);
    }
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'cnd_federal'],
        ['alvo_tipo' => 'participante', 'alvo_id' => $p2->id, 'fonte' => 'cnd_federal'],
        ['alvo_tipo' => 'cliente', 'alvo_id' => $cli->id, 'fonte' => 'cnd_federal'],
    ]);

    // 3 CNPJs × preço do plano c/ desconto (não menos).
    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes + ($precoPlano * 3));
});

it('settlement NÃO estorna se ao menos uma fonte do CNPJ voltou com sucesso', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);

    Cache::put("consulta_retry_charge:{$lote->id}:participante:{$p->id}", $precoPlano, 86400);
    // cnd_federal re-falhou, mas sintegra teve sucesso (não está em _fontes_erro) → mantém cobrança.
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $p->id, 'status' => 'sucesso',
        'resultado_dados' => [
            'sintegra' => ['status' => 'Ativo'],
            '_fontes_erro' => ['cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1]],
        ],
    ]);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'cnd_federal'],
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'sintegra'],
    ]);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes); // receita mantida (1 sucesso)
});

it('settlement NÃO estorna re-falha erro_participante — fonte faturada pelo provedor (espelha lote 220)', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);

    Cache::put("consulta_retry_charge:{$lote->id}:participante:{$p->id}", $precoPlano, 86400);
    // crf_fgts re-falhou com 620: a Caixa respondeu recusando os dados do CNPJ → a InfoSimples
    // fatura a chamada (billable: true) → a cobrança do retry fica com o usuário.
    gravarFontesErro($lote->id, $p->id, [
        'crf_fgts' => ['origem' => 'integracao', 'status' => 'erro_participante', 'codigo' => 620, 'tentativas' => 1],
    ]);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'crf_fgts'],
    ]);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes); // sem estorno
});

it('settlement misto (re-falha retry + re-falha erro_participante) mantém a cobrança do CNPJ', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $precoPlano = (int) ceil($lote->plano->custo_creditos * 0.5);

    Cache::put("consulta_retry_charge:{$lote->id}:participante:{$p->id}", $precoPlano, 86400);
    // cnd_federal re-falhou por instabilidade (não faturada), mas o crf_fgts 620 foi faturado
    // pelo provedor → o envelope do CNPJ é escalar, então a cobrança fica integral.
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 618, 'tentativas' => 1],
        'crf_fgts' => ['origem' => 'integracao', 'status' => 'erro_participante', 'codigo' => 620, 'tentativas' => 1],
    ]);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'cnd_federal'],
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'crf_fgts'],
    ]);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes); // sem estorno
});

// ---------------------------------------------------------------------------
// Task 4 — Endpoints HTTP (pendentes + retry)
// ---------------------------------------------------------------------------

it('GET retry/pendentes lista elegíveis e saldo do dono', function () {
    [$lote, $p, $user] = montarLoteComPlano();
    app(SaldoService::class)->add($user, 50);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
    ]);

    $this->actingAs($user)
        ->getJson("/app/consulta/lote/{$lote->id}/retry/pendentes")
        ->assertOk()
        ->assertJsonPath('elegiveis.0.fonte', 'cnd_federal')
        ->assertJsonPath('saldo', 50);
});

it('GET retry/pendentes de lote de outro user dá 404', function () {
    [$lote, $p, $user] = montarLoteComPlano();
    $outro = User::factory()->create();

    $this->actingAs($outro)
        ->getJson("/app/consulta/lote/{$lote->id}/retry/pendentes")
        ->assertNotFound();
});

it('POST retry sem saldo dá 402 e nada é cobrado/despachado', function () {
    Bus::fake();
    [$lote, $p, $user] = montarLoteComPlano();
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
    ]);

    $this->actingAs($user)
        ->postJson("/app/consulta/lote/{$lote->id}/retry")
        ->assertStatus(402);

    Bus::assertNothingBatched();
});

it('POST retry feliz cobra, despacha e responde novo saldo', function () {
    Bus::fake();
    config()->set('consultas.retry.desconto_pct', 50);
    [$lote, $p, $user] = montarLoteComPlano();
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
    ]);

    $this->actingAs($user)
        ->postJson("/app/consulta/lote/{$lote->id}/retry")
        ->assertOk()
        ->assertJsonPath('success', true);

    Bus::assertBatched(fn ($b) => $b->name === "consulta-retry-{$lote->id}");
});

// ---------------------------------------------------------------------------
// Task 6 — Regressão Model A (estorno do 600) + duplo-clique
// ---------------------------------------------------------------------------

it('código 600 (classe retry) agora acumula estorno no fechamento do lote', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    \Illuminate\Support\Facades\Http::fake([
        'api.infosimples.com/*' => \Illuminate\Support\Facades\Http::response(['code' => 600, 'code_message' => 'temporariamente indisponível'], 200),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_federal'], alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Certidões Federais'],
    );

    // Model A: retry passou a ser estornável → custoCreditos da cnd_federal vai pro estorno.
    expect((int) Cache::get("consulta_estorno:{$loteId}:participante:{$participanteId}"))
        ->toBe((int) config('consultas.fontes.cnd_federal', 2));
});

it('POST retry com lock ativo (duplo-clique) responde 409 sem cobrar duas vezes', function () {
    Bus::fake();
    config()->set('consultas.retry.desconto_pct', 50);
    [$lote, $p, $user] = montarLoteComPlano();
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
    ]);

    // simula a 1ª requisição ainda processando: lock já tomado
    Cache::lock("consulta_retry_lock:{$user->id}:{$lote->id}", 10)->get();
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    $this->actingAs($user)
        ->postJson("/app/consulta/lote/{$lote->id}/retry")
        ->assertStatus(409);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes); // não cobrou
    Bus::assertNothingBatched();
});

// ---------------------------------------------------------------------------
// GUARD — invariante: fonte em retry/fatal NÃO é persistida como blob (fica retriável).
// Verificado contra dado real (lote 213 prod): nenhuma fonte escapa hoje — os normalizers
// InfoSimples retornam [] em retry/fatal, então o job marca _fontes_erro. Este teste trava
// esse contrato (se um normalizer futuro passar a fabricar blob no erro, quebra aqui).
// ---------------------------------------------------------------------------

it('fonte com timeout (retry) não é persistida como blob e vira retriável', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    config()->set('consultas.cnd_estadual.ufs_cobertas', ['SP']);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');

    // code 610 = retry → normalizar devolve [] → job marca _fontes_erro, sem persistir blob.
    \Illuminate\Support\Facades\Http::fake([
        'api.infosimples.com/*' => \Illuminate\Support\Facades\Http::response(['code' => 610, 'code_message' => 'Tentativas excedidas'], 200),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['cnd_estadual'], alvo: ['cnpj' => '19131243000197', 'uf' => 'SP'],
        etapas: ['Preparando consulta', 'Certidões Estaduais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    $d = (array) $r->resultado_dados;
    expect($d['cnd_estadual'] ?? null)->toBeNull(); // NÃO persistido (re-consultável)
    expect($d['_fontes_erro']['cnd_estadual'] ?? null)->toMatchArray(['origem' => 'integracao', 'status' => 'retry', 'codigo' => 610]);
});

// ---------------------------------------------------------------------------
// Task 4 — Motivo de retry (orientação UI): agrupa os códigos InfoSimples da
// classe `retry` em 3 motivos acionáveis com rótulo/espera/orientação. Só os
// elegíveis recebem motivo; o agregado alimenta o banner da tela do lote.
// ---------------------------------------------------------------------------

it('classifica cada código InfoSimples retry no motivo certo', function () {
    $svc = app(RetryConsultaService::class);

    foreach ([605, 613, 614, 615, 618] as $codigo) {
        expect($svc->motivoDe($codigo)['motivo'])->toBe('origem_instavel');
    }
    foreach ([600, 610] as $codigo) {
        expect($svc->motivoDe($codigo)['motivo'])->toBe('tecnica_pontual');
    }
    expect($svc->motivoDe(609)['motivo'])->toBe('origem_persistente');
});

it('motivoDe expõe rótulo/espera/ícone/orientação da apresentação', function () {
    $info = app(RetryConsultaService::class)->motivoDe(615);

    expect($info['motivo'])->toBe('origem_instavel');
    expect($info['aguardar_minutos'])->toBe(30);
    expect($info)->toHaveKeys(['rotulo', 'icone', 'orientacao']);
});

it('cai no fallback tecnica_pontual quando o código retry não está mapeado', function () {
    expect(app(RetryConsultaService::class)->motivoDe(699)['motivo'])->toBe('tecnica_pontual');
});

it('anexa o motivo a cada fonte elegível em pendentesRetry', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
        'sintegra' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));
    $motivos = collect($out['elegiveis'])->pluck('motivo', 'fonte');

    expect($motivos['cnd_federal'])->toBe('origem_instavel');
    expect($motivos['sintegra'])->toBe('tecnica_pontual');
});

it('agrega os motivos presentes deduplicados (1 entrada por motivo) com apresentação', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
        'cnd_estadual' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 605, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    // dois códigos diferentes do MESMO motivo → 1 entrada só
    expect(array_keys($out['motivos']))->toBe(['origem_instavel']);
    expect($out['motivos']['origem_instavel']['aguardar_minutos'])->toBe(30);
    expect($out['motivos']['origem_instavel'])->toHaveKeys(['rotulo', 'icone', 'orientacao']);
});

it('não inclui fontes inelegíveis no agregado de motivos', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect($out['motivos'])->toBe([]);
});

// ---------------------------------------------------------------------------
// Task 5 — Tela de processamento durante a reconsulta (reuso do fluxo SSE):
// executar vira o lote p/ `processando` (+ tab_id novo); fechar restaura `finalizado`.
// ---------------------------------------------------------------------------

it('executar vira o lote para processando com tab_id novo (liga a tela de progresso)', function () {
    Bus::fake();
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);
    $tabAntigo = $lote->tab_id;

    app(RetryConsultaService::class)->executar($lote->fresh());

    $fresh = $lote->fresh();
    expect(ConsultaLote::normalizeStatus($fresh->status))->toBe(ConsultaLote::STATUS_PROCESSANDO);
    expect($fresh->tab_id)->not->toBe($tabAntigo);
    expect($fresh->tab_id)->not->toBeEmpty();
    Bus::assertBatched(fn ($b) => $b->name === "consulta-retry-{$lote->id}");
});

it('executar NÃO vira status nem cobra quando falta saldo (402)', function () {
    Bus::fake();
    [$lote, $p, $user] = montarLoteComPlano('compliance'); // sem créditos
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);

    expect(fn () => app(RetryConsultaService::class)->executar($lote->fresh()))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    expect(ConsultaLote::normalizeStatus($lote->fresh()->status))->toBe(ConsultaLote::STATUS_FINALIZADO);
    Bus::assertNothingBatched();
});

it('executar NÃO vira status quando não há CNPJ elegível (422)', function () {
    Bus::fake();
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
    ]);

    expect(fn () => app(RetryConsultaService::class)->executar($lote->fresh()))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    expect(ConsultaLote::normalizeStatus($lote->fresh()->status))->toBe(ConsultaLote::STATUS_FINALIZADO);
    Bus::assertNothingBatched();
});

it('reverte status e estorna se o dispatch do batch falhar (lote não fica preso)', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    app(SaldoService::class)->add($user, 100);
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    // força o despacho do batch a explodir DEPOIS do deduct + flip de status.
    Bus::shouldReceive('batch')->andThrow(new \RuntimeException('falha de fila'));

    expect(fn () => app(RetryConsultaService::class)->executar($lote->fresh()))
        ->toThrow(\RuntimeException::class);

    $fresh = $lote->fresh();
    expect(ConsultaLote::normalizeStatus($fresh->status))->toBe(ConsultaLote::STATUS_FINALIZADO); // restaurado
    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes); // estornado
});

it('fechar restaura o status do lote para finalizado após o settlement', function () {
    [$lote, $p, $user] = montarLoteComPlano('compliance');
    $lote->update(['status' => ConsultaLote::STATUS_PROCESSANDO]); // em reconsulta
    gravarFontesErro($lote->id, $p->id, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1],
    ]);

    app(FecharRetryService::class)->fechar($lote->id, [
        ['alvo_tipo' => 'participante', 'alvo_id' => $p->id, 'fonte' => 'cnd_federal'],
    ]);

    expect(ConsultaLote::normalizeStatus($lote->fresh()->status))->toBe(ConsultaLote::STATUS_FINALIZADO);
});

// ---------------------------------------------------------------------------
// Task 6 — Botão "Comunicar com o suporte" após a 1ª reconsulta falhar:
// pendentesRetry expõe `persistentes` (tentativas≥1) + `suporte`, COEXISTINDO com o retry.
// ---------------------------------------------------------------------------

it('suporte coexiste com o retry: fonte já reconsultada (tentativas≥1) segue elegível E vira persistente', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect($out['alvos'])->not->toBeEmpty(); // retry AINDA disponível (ilimitado)
    expect(collect($out['persistentes'])->pluck('fonte')->all())->toBe(['cnd_federal']);
    expect($out['suporte'])->not->toBeNull(); // suporte também
    expect($out['suporte']['contexto'])->toContain("Lote #{$loteId}");
    expect($out['suporte']['mensagem'])->toContain('615');
});

it('contexto de suporte conta CNPJs únicos, não fontes (1 CNPJ com 2 fontes = 1 CNPJ)', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 1],
        'sintegra' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 605, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect($out['persistentes'])->toHaveCount(2); // 2 fontes do mesmo CNPJ
    expect($out['suporte']['contexto'])->toContain('1 CNPJ(s)'); // mas 1 CNPJ só
});

it('suporte é null antes da 1ª reconsulta (tentativas 0); retry já disponível', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 615, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect($out['alvos'])->not->toBeEmpty(); // pode reconsultar
    expect($out['persistentes'])->toBe([]);
    expect($out['suporte'])->toBeNull(); // mas suporte só após tentar
});

// ---------------------------------------------------------------------------
// erro_participante (608/619/620) também é reconsultável — o botão Reconsultar
// fica SEMPRE disponível ao lado do "Comunicar com o suporte" (caso lote 218 prod).
// ---------------------------------------------------------------------------

it('erro_participante (620) é elegível com motivo dados_participante', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'erro_participante', 'codigo' => 620, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect(collect($out['elegiveis'])->pluck('fonte')->all())->toBe(['cnd_federal']);
    expect($out['elegiveis'][0]['motivo'])->toBe('dados_participante');
    expect($out['alvos'])->toHaveCount(1);
    expect(array_keys($out['motivos']))->toBe(['dados_participante']);
    expect($out['motivos']['dados_participante']['orientacao'])->toContain('cadastro');
});

it('erro_participante já reconsultado (tentativas 1) mantém o botão E o suporte coexistindo — espelha lote 218', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'erro_participante', 'codigo' => 620, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect($out['alvos'])->not->toBeEmpty(); // Reconsultar segue disponível (ilimitado)
    expect(collect($out['persistentes'])->pluck('fonte')->all())->toBe(['cnd_federal']);
    expect($out['suporte'])->not->toBeNull(); // suporte junto
    expect($out['suporte']['mensagem'])->toContain('620');
});

it('fatal e interno seguem inelegíveis mesmo com erro_participante elegível ao lado', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'erro_participante', 'codigo' => 620, 'tentativas' => 0],
        'cndt' => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
        'crf_fgts' => ['origem' => 'interno', 'status' => null, 'codigo' => null, 'tentativas' => 0],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect(collect($out['elegiveis'])->pluck('fonte')->all())->toBe(['cnd_federal']);
    expect(collect($out['inelegiveis'])->pluck('motivo', 'fonte')->all())
        ->toBe(['cndt' => 'fatal', 'crf_fgts' => 'interno']);
});

// ---------------------------------------------------------------------------
// Task 7 — Reconsulta escopada: progresso conta SÓ as fontes do retry (não o plano).
// ---------------------------------------------------------------------------

it('reconsulta escopada conta só as fontes do retry no progresso (não o plano inteiro)', function () {
    config()->set('consultas.cnd_estadual.ufs_cobertas', ['SP']);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'tok');
    \Illuminate\Support\Facades\Http::fake([
        'api.infosimples.com/*' => \Illuminate\Support\Facades\Http::response(['code' => 200, 'data' => [['x' => 1]]], 200),
    ]);
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    // Plano com VÁRIAS fontes, mas o retry é escopado a 1 → o progresso deve dizer "1 de 1".
    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-x',
        consultasIncluidas: ['cadastro', 'cnd_federal', 'crf_fgts', 'cndt', 'cnd_estadual'],
        alvo: ['cnpj' => '19131243000197', 'uf' => 'SP'],
        etapas: ['Preparando consulta', 'Dados cadastrais', 'Certidões Federais', 'Certidões Estaduais'],
        somenteFontes: ['cnd_estadual'],
    );

    $p = \Illuminate\Support\Facades\Cache::get("progresso:{$userId}:tab-x");
    expect($p['fonte_total'])->toBe(1);
    expect($p['fonte_indice'])->toBe(1);
    expect($p['mensagem'])->toContain('(1 de 1)');
});
