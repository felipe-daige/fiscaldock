<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaLote;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

function criarUserComParticipante(float $saldo = 10.0): array
{
    $user = User::factory()->create(['credits' => $saldo]);
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$user, $pid];
}

it('executa lote avulso por fontes: debita preco por fonte, grava selecao e despacha com precosVenda', function () {
    Bus::fake();
    Http::fake();
    [$user, $pid] = criarUserComParticipante(10.0);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal', 'cndt'],
        'tab_id' => 'tab-avulsa',
    ])->assertOk()->assertJson(['success' => true, 'valor_cobrado_reais' => 2.00]);

    $lote = ConsultaLote::find($resp->json('consulta_lote_id'));
    expect($lote->plano_id)->toBeNull()
        ->and($lote->fontes_selecionadas)->toBe(['cnd_federal', 'cndt'])
        ->and($lote->ehAvulsoPorFontes())->toBeTrue()
        ->and((float) $lote->creditos_cobrados)->toBe(2.00)
        ->and((float) $user->fresh()->credits)->toBe(8.00);

    Bus::assertBatched(function ($batch) {
        $job = collect($batch->jobs)->first();

        return $job instanceof ProcessarConsultaJob
            && $job->precosVenda === ['cnd_federal' => 1.00, 'cndt' => 1.00]
            // Atributos derivam de volta cadastro + selecionadas; etapas dinâmicas por grupo.
            && in_array('cnd_federal', $job->consultasIncluidas, true)
            && array_column($job->etapas, 'chave') === ['inicializacao', 'cadastrais', 'certidoes_federais'];
    });
    Http::assertNothingSent();
});

it('rejeita fonte desconhecida ou nao pronta', function () {
    Bus::fake();
    [$user, $pid] = criarUserComParticipante();

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal', 'fonte_inexistente'],
        'tab_id' => 't',
    ])->assertStatus(422);

    // Gate desligado → nenhuma fonte pronta → toda seleção é recusada.
    config()->set('consultas.infosimples_ativo', false);
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal'],
        'tab_id' => 't',
    ])->assertStatus(422);

    Bus::assertNothingBatched();
});

it('recusa com 402 quando o saldo nao cobre a selecao', function () {
    Bus::fake();
    [$user, $pid] = criarUserComParticipante(1.50);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal', 'cndt'],
        'tab_id' => 't',
    ])->assertStatus(402);

    expect((float) $user->fresh()->credits)->toBe(1.50);
    Bus::assertNothingBatched();
});

it('calcular-custo-fontes retorna preview com preco por alvo e total', function () {
    [$user] = criarUserComParticipante(5.0);
    config()->set('advocacia.precos.sintegra', 2.00);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/calcular-custo', [
        'fontes' => ['cnd_federal', 'sintegra'],
        'quantidade' => 3,
    ])->assertOk()->assertJson([
        'success' => true,
        'preco_por_alvo_reais' => 3.00,
        'custo_total_reais' => 9.00,
        'saldo_suficiente' => false,
    ]);
});

it('showLote de lote avulso NAO redireciona pro clearance e deriva etapas da selecao', function () {
    [$user, $pid] = criarUserComParticipante();

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null,
        'fontes_selecionadas' => ['cnd_federal'],
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1, 'creditos_cobrados' => 1.00, 'tab_id' => 't',
    ]);
    $lote->participantes()->attach($pid);

    $this->actingAs($user)->get("/app/consulta/lote/{$lote->id}")
        ->assertOk()
        ->assertViewHas('etapas', fn ($etapas) => array_column($etapas, 'chave') === ['inicializacao', 'cadastrais', 'certidoes_federais']);
});

it('executa avulsa REAL (batch sync): persiste certidao judicial e passa pelo throttle', function () {
    // Sem Bus::fake — batch roda inline no driver sync, exercitando o pipeline inteiro
    // (ProcessarConsultaJob → ThrottleProvider → InfoSimplesProvider → normalizer → persistência).
    Http::fake([
        'minhareceita.org/*' => Http::response([
            'razao_social' => 'EMPRESA REAL', 'descricao_situacao_cadastral' => 'ATIVA', 'situacao_cadastral' => 2,
            'uf' => 'SP', 'municipio' => 'SAO PAULO', 'qsa' => [], 'cnaes_secundarios' => [],
        ], 200),
        'api.infosimples.com/*' => Http::response([
            'code' => 200, 'code_message' => 'ok',
            'data' => [['tipo' => 'Negativa', 'numero_certidao' => 'STJ-999', 'site_receipt' => null]],
            'data_count' => 1, 'errors' => [], 'site_receipts' => [],
        ], 200),
    ]);

    // Spy no throttle: fonte judicial nova TEM que aguardar o rate-limit do InfoSimples
    // (1 req/s), igual às fontes da Consulta CNPJ.
    $throttle = Mockery::spy(\App\Services\Consultas\ThrottleProvider::class);
    app()->instance(\App\Services\Consultas\ThrottleProvider::class, $throttle);

    [$user, $pid] = criarUserComParticipante(10.0);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['certidao_stj'],
        'tab_id' => 't-real',
    ])->assertOk()->assertJson(['success' => true]);

    $loteId = $resp->json('consulta_lote_id');
    expect(ConsultaLote::find($loteId)->status)->toBe('concluido');

    $r = \App\Models\ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['certidao_stj']['status'])->toBe('Negativa')
        ->and($r->resultado_dados['certidao_stj']['certidao_codigo'])->toBe('STJ-999')
        ->and($r->resultado_dados['razao_social'])->toBe('EMPRESA REAL');

    $throttle->shouldHaveReceived('aguardar')->with('infosimples')->atLeast()->once();
});

it('CEAT recebe nome (razao social) e uf nos params — cadastro sobrescreve a razao do banco', function () {
    // Regressao do smoke 606: CEAT exige `nome`. O cadastro (minhareceita) roda antes e injeta
    // a razao oficial da RFB no alvo; o params da CEAT tem que sair com nome + cnpj.
    $capturado = [];
    Http::fake(function ($request) use (&$capturado) {
        $url = $request->url();
        if (str_contains($url, 'minhareceita.org')) {
            return Http::response([
                'razao_social' => 'RAZAO OFICIAL RFB', 'descricao_situacao_cadastral' => 'ATIVA',
                'situacao_cadastral' => 2, 'uf' => 'SP', 'municipio' => 'SAO PAULO', 'qsa' => [], 'cnaes_secundarios' => [],
            ], 200);
        }
        if (str_contains($url, '/tribunal/trt2/ceat')) {
            $capturado = $request->data();
        }

        return Http::response([
            'code' => 200, 'code_message' => 'ok',
            'data' => [['nada_consta' => true, 'conseguiu_emitir_certidao_negativa' => true, 'numero_certidao' => 'CEAT-1']],
            'data_count' => 1, 'errors' => [], 'site_receipts' => [],
        ], 200);
    });

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'RAZAO DO BANCO',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['ceat_trt'],
        'tab_id' => 't-ceat',
    ])->assertOk();

    expect($capturado['nome'] ?? null)->toBe('RAZAO OFICIAL RFB')
        ->and($capturado['cnpj'] ?? null)->toBe('19131243000197');
});

it('tela /app/consulta/fontes renderiza grupos, precos e saldo', function () {
    [$user] = criarUserComParticipante(7.50);

    $this->actingAs($user)->get('/app/consulta/fontes')
        ->assertOk()
        ->assertSee('Consulta por Fontes')
        ->assertSee('CND Federal (Receita/PGFN)')
        ->assertSee('SINTEGRA')
        ->assertSee("R\$\u{A0}1,00 por CNPJ") // Dinheiro::brl usa NBSP entre R$ e o número
        ->assertViewHas('gruposFontes', fn ($g) => isset($g['fiscal']) && count($g['fiscal']['fontes']) === 6);
});

it('prefill de re-emissao: ?fonte=&documento= pre-marca fonte e alvo do usuario', function () {
    [$user, $pid] = criarUserComParticipante();

    $this->actingAs($user)
        ->get('/app/consulta/fontes?fonte=certidao_stj&documento=19131243000197')
        ->assertOk()
        ->assertViewHas('prefill', fn ($p) => $p['fontes'] === ['certidao_stj']
            && $p['alvo']['tipo'] === 'participante'
            && $p['alvo']['id'] === $pid);

    // Documento de OUTRO usuário nunca resolve alvo.
    $outro = User::factory()->create();
    $this->actingAs($outro)
        ->get('/app/consulta/fontes?fonte=certidao_stj&documento=19131243000197')
        ->assertOk()
        ->assertViewHas('prefill', fn ($p) => $p['alvo'] === null);
});

it('sidebar CONTENCIOSO aparece para advogado e some para contador', function () {
    $advogado = User::factory()->create(['persona' => 'advogado']);
    $this->actingAs($advogado)->get('/app/consulta/fontes')
        ->assertOk()
        ->assertSee('CONTENCIOSO');

    $contador = User::factory()->create(['persona' => 'contador']);
    $this->actingAs($contador)->get('/app/consulta/fontes')
        ->assertOk()
        ->assertDontSee('CONTENCIOSO');
});

it('showLote de avulso CONCLUIDO com fontes falhas nao estoura 500 (regressao lote 260)', function () {
    // Bug real de prod: modal de retry renderizava server-side com $lote->plano->nome (null em
    // lote avulso) quando havia fontes elegíveis a retry → 500 na página inteira.
    [$user, $pid] = criarUserComParticipante();

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null,
        'fontes_selecionadas' => ['certidao_stj', 'falencias'],
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 2.00, 'tab_id' => 't',
    ]);
    $lote->participantes()->attach($pid);

    \App\Models\ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $pid,
        'user_id' => $user->id,
        'status' => 'sucesso',
        'consultado_em' => now(),
        'resultado_dados' => [
            'razao_social' => 'PART',
            'certidao_stj' => ['status' => 'Negativa'],
            'consultas_realizadas' => ['certidao_stj'],
            '_fontes_erro' => ['falencias' => ['codigo' => 615, 'origem' => 'integracao', 'status' => 'retry', 'tentativas' => 1]],
        ],
    ]);

    $this->actingAs($user)->get("/app/consulta/lote/{$lote->id}")
        ->assertOk()
        ->assertViewHas('retryPendentes', fn ($r) => ($r['elegiveis'] ?? null) === []);
});

it('retry manual bloqueado em lote avulso (auto-retry in-job cobre transitorio)', function () {
    [$user] = criarUserComParticipante();

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null,
        'fontes_selecionadas' => ['cnd_federal'],
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 1.00, 'tab_id' => 't',
    ]);

    $this->actingAs($user)->getJson("/app/consulta/lote/{$lote->id}/retry/pendentes")
        ->assertOk()->assertJson(['elegiveis' => [], 'total_preco_creditos' => 0]);

    $this->actingAs($user)->postJson("/app/consulta/lote/{$lote->id}/retry")
        ->assertStatus(422);
});
