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
    // As PF nativas ficam atrás do gate de smoke (params ainda não confirmados no painel);
    // os testes as liberam explicitamente para exercitar o pipeline.
    config()->set('advocacia.fontes_publicas_liberadas', ['cadastro_pf', 'quitacao_eleitoral']);
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

function criarParticipanteCpf(User $user, string $cpf = '52998224725', string $nome = 'MARIA DA SILVA'): int
{
    return DB::table('participantes')->insertGetId([
        'user_id' => $user->id,
        'documento' => $cpf,
        'tipo_documento' => 'PF',
        'razao_social' => $nome,
        'uf' => 'MS',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
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

    // Fonte futura aparece na vitrine/admin como "Em manutenção", mas nunca entra no carrinho,
    // não cria lote e não pode ser forçada por POST antes da validação operacional.
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['pgfn_devedores'],
        'tab_id' => 't-futura',
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Fontes indisponíveis para consulta avulsa: pgfn_devedores');

    // Gate desligado → nenhuma fonte pronta → toda seleção é recusada.
    config()->set('consultas.infosimples_ativo', false);
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal'],
        'tab_id' => 't',
    ])->assertStatus(422);

    Bus::assertNothingBatched();
});

it('IBAMA autuacoes exige ano de qualquer alvo antes de cobrar', function () {
    Bus::fake();
    [$user, $pid] = criarUserComParticipante();
    config()->set('advocacia.fontes_publicas_liberadas', ['ibama_autuacoes']);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['ibama_autuacoes'],
        'tab_id' => 'ibama-sem-ano',
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Complete os dados do alvo antes de consultar: ano.');

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['ibama_autuacoes'],
        'dados_pf' => [[
            'tipo' => 'participante',
            'id' => $pid,
            'ano' => 2024,
        ]],
        'tab_id' => 'ibama-com-ano',
    ])->assertOk()->assertJsonPath('valor_cobrado_reais', 1);

    Bus::assertBatched(function ($batch) {
        $job = collect($batch->jobs)->first();

        return $job instanceof ProcessarConsultaJob
            && $job->alvo['ano'] === '2024'
            && in_array('ibama_autuacoes', $job->consultasIncluidas, true);
    });
});

it('BCB valores a receber recebe abertura cadastrada da PJ', function () {
    Bus::fake();
    [$user, $pid] = criarUserComParticipante();
    DB::table('participantes')->where('id', $pid)->update(['data_inicio_atividade' => '2014-09-02']);
    config()->set('advocacia.fontes_publicas_liberadas', ['bcb_valores_receber']);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['bcb_valores_receber'],
        'tab_id' => 'bcb-pj',
    ])->assertOk();

    $jobCapturado = null;
    Bus::assertBatched(function ($batch) use (&$jobCapturado) {
        $jobCapturado = collect($batch->jobs)->first();

        return true;
    });
    expect($jobCapturado)->toBeInstanceOf(ProcessarConsultaJob::class)
        ->and($jobCapturado->alvo['data_inicio_atividade'])->toBe('2014-09-02')
        ->and($jobCapturado->consultasIncluidas)->toContain('bcb_valores_receber')
        ->and($jobCapturado->consultasIncluidas)->toContain('situacao_cadastral');
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

it('executa fonte nativa PF sem injetar o cadastro CNPJ no pipeline', function () {
    Bus::fake();
    Http::fake();
    $user = User::factory()->create(['credits' => 10.0]);
    $pid = criarParticipanteCpf($user);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cadastro_pf'],
        'dados_pf' => [[
            'tipo' => 'participante',
            'id' => $pid,
            'nome' => 'Maria da Silva',
            'birthdate' => '1980-05-10',
        ]],
        'tab_id' => 'tab-pf',
    ])->assertOk()->assertJson([
        'success' => true,
        'valor_cobrado_reais' => 1.00,
    ]);

    $lote = ConsultaLote::findOrFail($resp->json('consulta_lote_id'));
    expect($lote->fontes_selecionadas)->toBe(['cadastro_pf'])
        ->and((float) $user->fresh()->credits)->toBe(9.00);

    Bus::assertBatched(function ($batch) use ($pid) {
        $job = collect($batch->jobs)->first();

        return $job instanceof ProcessarConsultaJob
            && $job->alvoTipo === 'participante'
            && $job->alvoId === $pid
            && $job->alvo['tipo_pessoa'] === 'PF'
            && $job->alvo['documento'] === '52998224725'
            && $job->alvo['cpf'] === '52998224725'
            && $job->alvo['cnpj'] === null
            && $job->alvo['birthdate'] === '1980-05-10'
            && $job->consultasIncluidas === ['cadastro_pf']
            && array_column($job->etapas, 'chave') === ['inicializacao', 'cadastrais'];
    });
    Http::assertNothingSent();
});

it('executa pipeline PF real, persiste contexto e registra certidao TSE para CPF', function () {
    $capturados = [];
    Http::fake(function ($request) use (&$capturados) {
        $capturados[] = ['url' => $request->url(), 'data' => $request->data()];

        if (str_contains($request->url(), 'receita-federal/cpf')) {
            return Http::response([
                'code' => 200,
                'code_message' => 'ok',
                'data' => [[
                    'normalizado_cpf' => '52998224725',
                    'nome' => 'MARIA OFICIAL',
                    'situacao_cadastral' => 'REGULAR',
                    'normalizado_data_nascimento' => '10/05/1980',
                ]],
            ], 200);
        }

        return Http::response([
            'code' => 200,
            'code_message' => 'ok',
            'data' => [[
                'quite' => true,
                'autenticidade' => 'TSE-PF-1',
                'emissao_datahora' => '23/07/2026 10:00:00',
                'nome' => 'MARIA OFICIAL',
            ]],
        ], 200);
    });

    $throttle = Mockery::spy(\App\Services\Consultas\ThrottleProvider::class);
    app()->instance(\App\Services\Consultas\ThrottleProvider::class, $throttle);

    $user = User::factory()->create(['credits' => 10.0]);
    $pid = criarParticipanteCpf($user);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cadastro_pf', 'quitacao_eleitoral'],
        'dados_pf' => [[
            'tipo' => 'participante',
            'id' => $pid,
            'nome' => 'Maria digitada',
            'birthdate' => '1980-05-10',
        ]],
        'tab_id' => 'tab-pf-real',
    ])->assertOk()->assertJson(['valor_cobrado_reais' => 2.00]);

    $loteId = $resp->json('consulta_lote_id');
    $resultado = \App\Models\ConsultaResultado::where('consulta_lote_id', $loteId)->firstOrFail();

    expect(ConsultaLote::findOrFail($loteId)->status)->toBe('concluido')
        ->and($resultado->resultado_dados['_alvo_contexto']['tipo_pessoa'])->toBe('PF')
        ->and($resultado->resultado_dados['_alvo_contexto']['birthdate'])->toBe('1980-05-10')
        ->and($resultado->resultado_dados['cadastro_pf']['situacao_cadastral'])->toBe('REGULAR')
        ->and($resultado->resultado_dados['quitacao_eleitoral']['status'])->toBe('Negativa')
        ->and($resultado->resultado_dados['consultas_realizadas'])
        ->toBe(['cadastro_pf', 'quitacao_eleitoral']);

    expect(DB::table('certidoes')
        ->where('consulta_lote_id', $loteId)
        ->where('alvo_documento', '52998224725')
        ->where('tipo', 'quitacao_eleitoral')
        ->value('status'))->toBe('Negativa');

    $cpfRequest = collect($capturados)->first(fn ($r) => str_contains($r['url'], 'receita-federal/cpf'));
    $tseRequest = collect($capturados)->first(fn ($r) => str_contains($r['url'], 'tribunal/tse/certidao'));
    expect($cpfRequest['data']['cpf'] ?? null)->toBe('52998224725')
        ->and($cpfRequest['data']['birthdate'] ?? null)->toBe('1980-05-10')
        ->and($tseRequest['data']['cpf'] ?? null)->toBe('52998224725')
        ->and($tseRequest['data']['name'] ?? null)->toBe('MARIA OFICIAL');

    $this->actingAs($user)->get("/app/consulta/lote/{$loteId}")
        ->assertOk()
        ->assertViewHas('etapas', fn (array $etapas) => array_column($etapas, 'chave')
            === ['inicializacao', 'cadastrais', 'certidoes_judiciais'])
        ->assertViewHas('resultados', function ($resultados) {
            $linha = $resultados->getCollection()->first();

            return ($linha['documento_formatado'] ?? null) === '529.982.247-25'
                && ($linha['situacao_cadastral'] ?? null) === 'REGULAR'
                && collect($linha['certidoes'] ?? [])->contains(
                    fn (array $certidao) => ($certidao['chave'] ?? null) === 'quitacao_eleitoral',
                );
        });

    $throttle->shouldHaveReceived('aguardar')->with('infosimples')->twice();
});

it('rejeita CPF invalido campos PF ausentes e fonte PJ antes de debitar', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 10.0]);
    $cpfValido = criarParticipanteCpf($user);
    $cpfInvalido = criarParticipanteCpf($user, '11111111111', 'CPF INVÁLIDO');

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$cpfInvalido],
        'fontes' => ['cadastro_pf'],
        'dados_pf' => [[
            'tipo' => 'participante',
            'id' => $cpfInvalido,
            'nome' => 'CPF Inválido',
            'birthdate' => '1980-05-10',
        ]],
        'tab_id' => 'pf-invalido',
    ])->assertStatus(422)->assertJsonPath('error', 'CPF inválido no alvo selecionado.');

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$cpfValido],
        'fontes' => ['cadastro_pf'],
        'dados_pf' => [[
            'tipo' => 'participante',
            'id' => $cpfValido,
            'nome' => 'Maria da Silva',
        ]],
        'tab_id' => 'pf-sem-nascimento',
    ])->assertStatus(422)->assertJsonPath('error', 'Complete os dados da pessoa física antes de consultar: birthdate.');

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$cpfValido],
        'fontes' => ['cndt'],
        'tab_id' => 'pf-fonte-pj',
    ])->assertStatus(422)->assertJsonPath('error', 'Fontes incompatíveis com alvo PF: cndt');

    expect((float) $user->fresh()->credits)->toBe(10.0)
        ->and(ConsultaLote::where('user_id', $user->id)->count())->toBe(0);
    Bus::assertNothingBatched();
});

it('fontes PF sensiveis nao podem ser vendidas com as flags desligadas', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 10.0]);
    $pid = criarParticipanteCpf($user);

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['antecedentes_pf', 'mandado_prisao'],
        'tab_id' => 'pf-sensivel',
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Fontes indisponíveis para consulta avulsa: antecedentes_pf, mandado_prisao');

    expect((float) $user->fresh()->credits)->toBe(10.0);
    Bus::assertNothingBatched();
});

it('preview de custo rejeita fonte incompativel com o tipo do alvo', function () {
    [$user] = criarUserComParticipante();

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/calcular-custo', [
        'fontes' => ['cadastro'],
        'quantidade' => 1,
        'tipos_pessoa' => ['PF'],
    ])->assertStatus(422)->assertJsonPath('error', 'Fontes incompatíveis com alvo PF: cadastro');
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

it('showLote PF sem cadastro nao inventa etapa cadastral de CNPJ', function () {
    $user = User::factory()->create();
    $pid = criarParticipanteCpf($user);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => null,
        'fontes_selecionadas' => ['quitacao_eleitoral'],
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 1.00,
        'tab_id' => 't-pf-etapas',
    ]);
    $lote->participantes()->attach($pid);

    $this->actingAs($user)->get("/app/consulta/lote/{$lote->id}")
        ->assertOk()
        ->assertViewHas('etapas', fn (array $etapas) => array_column($etapas, 'chave')
            === ['inicializacao', 'certidoes_judiciais']);
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

it('CEAT recebe nome (razao social), cnpj e cpf_solicitante do dono da conta nos params', function () {
    // Regressao do smoke 606: CEAT exige `nome` (lote 260) + `cpf_solicitante` (lote 261, TRT24).
    // O cadastro (minhareceita) injeta a razao oficial da RFB; o job injeta o CPF do DONO DA CONTA
    // (users.cpf) como cpf_solicitante. Params tem que sair com nome + cnpj + cpf_solicitante do user.
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

    $user = User::factory()->create(['credits' => 10.0, 'cpf' => '39053344705']);
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
        ->and($capturado['cnpj'] ?? null)->toBe('19131243000197')
        ->and($capturado['cpf_solicitante'] ?? null)->toBe('39053344705');
});

it('tela /app/consulta/painel renderiza grupos, precos e saldo', function () {
    [$user] = criarUserComParticipante(7.50);

    $this->actingAs($user)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('Nova Consulta')
        ->assertSee('CND Federal (Receita/PGFN)')
        ->assertSee('SINTEGRA')
        ->assertSee('Cadastro e situação do CPF (Receita Federal)')
        ->assertSee('Quitação Eleitoral (TSE)')
        ->assertSee('Antecedentes Criminais (Polícia Federal)')
        ->assertSee('Mandados de Prisão vigentes (CNJ/BNMP)')
        ->assertSee('Dívida Ativa — Lista de Devedores PGFN')
        ->assertSee('IBAMA — Certidão de Embargos')
        ->assertSee('Em manutenção')
        ->assertSee('data-documentos-label="CPF"', false)
        ->assertSee('data-documentos-label="CNPJ"', false)
        ->assertSee("R\$\u{A0}1,00") // Dinheiro::brl usa NBSP entre R$ e o número; "por CNPJ" em span à parte
        ->assertSee('Situação Cadastral (grátis)') // cadastro grátis selecionável
        ->assertViewHas('gruposFontes', fn ($g) => isset($g['pessoa_fisica'])
            && count($g['pessoa_fisica']['fontes']) === 4
            && isset($g['fiscal'])
            && count($g['fiscal']['fontes']) === 10
            && collect($g['passivo']['fontes'])->firstWhere('chave', 'pgfn_devedores')['selecionavel'] === false
            && collect($g['ambiental']['fontes'])->firstWhere('chave', 'ibama_embargos')['selecionavel'] === false);
});

it('prefill de re-emissao: ?fonte=&documento= pre-marca fonte e alvo do usuario', function () {
    [$user, $pid] = criarUserComParticipante();

    $this->actingAs($user)
        ->get('/app/consulta/painel?fonte=certidao_stj&documento=19131243000197')
        ->assertOk()
        ->assertViewHas('prefill', fn ($p) => $p['fontes'] === ['certidao_stj']
            && $p['alvo']['tipo'] === 'participante'
            && $p['alvo']['id'] === $pid);

    // Documento de OUTRO usuário nunca resolve alvo.
    $outro = User::factory()->create();
    $this->actingAs($outro)
        ->get('/app/consulta/painel?fonte=certidao_stj&documento=19131243000197')
        ->assertOk()
        ->assertViewHas('prefill', fn ($p) => $p['alvo'] === null);
});

it('prefill e listagem de alvos aceitam CPF somente na tela avulsa', function () {
    $user = User::factory()->create();
    $pid = criarParticipanteCpf($user);

    $this->actingAs($user)
        ->get('/app/consulta/painel?fonte=cadastro_pf&documento=52998224725')
        ->assertOk()
        ->assertViewHas('prefill', fn ($p) => $p['fontes'] === ['cadastro_pf']
            && $p['alvo']['tipo'] === 'participante'
            && $p['alvo']['id'] === $pid
            && $p['alvo']['tipoPessoa'] === 'PF');

    $this->actingAs($user)
        ->getJson('/app/consulta/nova/participantes?tipo_documento=PF&permitir_cpf=1&per_page=10')
        ->assertOk()
        ->assertJsonPath('data.0.id', $pid)
        ->assertJsonPath('data.0.pode_consultar', true);

    $this->actingAs($user)
        ->getJson('/app/consulta/nova/participantes?tipo_documento=PF&per_page=10')
        ->assertOk()
        ->assertJsonPath('data.0.pode_consultar', false);
});

it('sidebar CONTENCIOSO aparece para advogado e some para contador', function () {
    $advogado = User::factory()->create(['persona' => 'advogado']);
    $this->actingAs($advogado)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('CONTENCIOSO');

    $contador = User::factory()->create(['persona' => 'contador']);
    $this->actingAs($contador)->get('/app/consulta/painel')
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

it('seleção só de fontes gratuitas respeita o cap de consultas grátis (sem 1ª compra)', function () {
    Bus::fake();
    Http::fake();
    config()->set('trial.limite_consultas_gratuito', 3);

    [$user, $pid] = criarUserComParticipante(0.0);
    $outros = collect(range(1, 3))->map(fn ($i) => DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '1913124300019'.$i, 'razao_social' => "P{$i}",
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]))->all();

    // `cadastro` custa R$ 0,00, então `hasEnough($user, 0)` é sempre true: sem o cap, a consulta
    // cadastral seria ILIMITADA e grátis pra quem nunca comprou — e esta é a tela principal.
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => array_merge([$pid], $outros), // 4 alvos > limite 3
        'fontes' => ['cadastro'],
        'tab_id' => 'tab-gratis',
    ])->assertStatus(402)->assertJsonPath('cap_gratuito.limite', 3);

    expect(ConsultaLote::where('user_id', $user->id)->count())->toBe(0);

    // Dentro do limite passa, e o lote grátis PASSA A CONTAR no cap (creditos_cobrados = 0).
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cadastro'],
        'tab_id' => 'tab-gratis-2',
    ])->assertOk();

    $cap = app(\App\Services\PricingCatalogService::class)->gratuitoCapStatus($user->fresh());
    expect($cap['usados'])->toBe(1)->and($cap['restantes'])->toBe(2);
});

it('cap grátis não se aplica a seleção paga nem a quem já comprou', function () {
    Bus::fake();
    Http::fake();
    config()->set('trial.limite_consultas_gratuito', 1);

    [$user, $pid] = criarUserComParticipante(10.0);

    // Seleção PAGA passa pelo saldo, não pelo cap — o cap é só do caminho sem custo.
    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cnd_federal'],
        'tab_id' => 'tab-paga',
    ])->assertOk();

    // Lote pago (creditos_cobrados > 0) não entra na conta das gratuitas.
    expect(app(\App\Services\PricingCatalogService::class)->gratuitoCapStatus($user->fresh())['usados'])->toBe(0);
});

it('persiste data_inicio_atividade vinda do cadastro no contexto do alvo para o retry manual', function () {
    // Regressão: o contexto do alvo é gravado ANTES do loop de fontes, quando o alvo ainda não
    // tem a data de abertura. Sem a re-gravação ao final, a reconsulta manual do BCB Valores a
    // Receber ficava INDISPONÍVEL num alvo cuja data só existiu no cadastro daquele lote.
    config()->set('advocacia.fontes_publicas_liberadas', ['bcb_valores_receber']);

    Http::fake([
        'minhareceita.org/*' => Http::response([
            'cnpj' => '19131243000197',
            'razao_social' => 'PART OFICIAL',
            'data_inicio_atividade' => '2010-03-15',
            'uf' => 'SP',
            'municipio' => 'SAO PAULO',
        ], 200),
        '*bcb/valores-receber*' => Http::response([
            'code' => 200,
            'code_message' => 'ok',
            'data' => [['possui_valores_receber' => true]],
        ], 200),
    ]);

    app()->instance(
        \App\Services\Consultas\ThrottleProvider::class,
        Mockery::spy(\App\Services\Consultas\ThrottleProvider::class),
    );

    [$user, $pid] = criarUserComParticipante(10.0);
    // O participante NÃO tem a data no cadastro local: ela só existe na resposta da minhareceita.
    expect(DB::table('participantes')->where('id', $pid)->value('data_inicio_atividade'))->toBeNull();

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['bcb_valores_receber'],
        'tab_id' => 'tab-bcb-pj',
    ])->assertOk();

    $resultado = \App\Models\ConsultaResultado::where('consulta_lote_id', $resp->json('consulta_lote_id'))
        ->firstOrFail();

    expect($resultado->resultado_dados['_alvo_contexto']['data_inicio_atividade'])->toBe('2010-03-15')
        ->and($resultado->resultado_dados['bcb_valores_receber']['status'])->toBe('Positiva');

    // O retry manual reconstrói o alvo a partir do contexto — a fonte volta a ser aplicável.
    $alvo = (new ReflectionMethod(\App\Services\Consultas\RetryConsultaService::class, 'resolverAlvo'))
        ->invoke(
            app(\App\Services\Consultas\RetryConsultaService::class),
            $resp->json('consulta_lote_id'),
            'participante',
            $pid,
        );

    expect((new \App\Services\Consultas\Fontes\Advocacia\BcbValoresReceberFonte)->aplicavelPara($alvo))
        ->toBeTrue();
});
