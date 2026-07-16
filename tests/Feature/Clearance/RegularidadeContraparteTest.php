<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\Clearance\RegularidadeContraparteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function rcParticipante(User $u, string $doc, string $razao = 'FORNECEDOR LTDA', ?string $uf = 'SP'): Participante
{
    return Participante::create([
        'user_id' => $u->id, 'documento' => $doc, 'tipo_documento' => 'PJ',
        'razao_social' => $razao, 'origem_tipo' => 'MANUAL', 'uf' => $uf,
    ]);
}

beforeEach(function () {
    // Camada A depende do mesmo gate da Consulta CNPJ (InfoSimples ligado + token).
    config([
        'consultas.infosimples_ativo' => true,
        'consultas.providers.infosimples.token' => 'test-token',
        'clearance.full.frescura_dias' => 30,
    ]);
    Bus::fake();
});
it('sem cobertura InfoSimples não dispara nada', function () {
    config(['consultas.infosimples_ativo' => false]);
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(0)
        ->and($out['lote_id'])->toBeNull();
    Bus::assertNothingBatched();
});

it('participante stale dispara a consulta das 3 fontes', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(1)
        ->and($out['reusados'])->toBe(0)
        ->and($out['lote_id'])->not->toBeNull();

    Bus::assertBatched(function ($batch) use ($p) {
        $job = $batch->jobs->first();

        return $batch->jobs->count() === 1
            && $job->alvoTipo === 'participante'
            && $job->alvoId === $p->id
            && $job->consultasIncluidas === ['situacao_cadastral', 'sintegra', 'cnd_federal'];
    });

    // Lote interno: sem plano e sem cobrança neste nível (billing é per-CNPJ, fase 5).
    $lote = ConsultaLote::find($out['lote_id']);
    expect($lote->plano_id)->toBeNull()
        ->and($lote->creditos_cobrados)->toBe(0.0)
        ->and((int) $lote->participantes()->count())->toBe(1);
});

it('participante com score fresco (3 fontes) é reusado, não reconsulta', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');
    ParticipanteScore::create([
        'participante_id' => $p->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now()->subDays(5),
        // Fresco = data DENTRO da janela + as 3 fontes presentes (ver temTodasAsFontes).
        'dados_consultados' => [
            'situacao_cadastral' => 'ATIVA',
            'sintegra' => ['situacao' => 'Habilitada'],
            'cnd_federal' => ['status' => 'Negativa'],
        ],
    ]);

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(0)
        ->and($out['reusados'])->toBe(1);
    Bus::assertNothingBatched();
});

// Regressão real (pega no lote 235 em prod, 2026-07-13): o gate de frescura olhava só a DATA.
// Um participante_scores recente vindo de uma Consulta CNPJ de plano menor (só cadastral) era
// tratado como "fresco" — e a tela mostrava "Situação cadastral: Ativa · SINTEGRA — · CND Federal —".
// Quem pagou por 3 fontes recebia 1. Fresco agora exige a data E as 3 fontes.
it('cache PARCIAL (só cadastral) não é fresco — reconsulta pra entregar as 3 fontes', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');
    ParticipanteScore::create([
        'participante_id' => $p->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now()->subDays(2), // recentíssimo…
        'dados_consultados' => ['situacao_cadastral' => 'ATIVA'], // …mas sem SINTEGRA nem CND
    ]);

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(1)
        ->and($out['reusados'])->toBe(0);
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1);
});

it('cache COMPLETO (3 fontes) é reusado sem consultar', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');
    ParticipanteScore::create([
        'participante_id' => $p->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now()->subDays(2),
        'dados_consultados' => [
            'situacao_cadastral' => 'ATIVA',
            'sintegra' => ['situacao' => 'Habilitada'],
            'cnd_federal' => ['status' => 'Negativa'],
        ],
    ]);

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(0)
        ->and($out['reusados'])->toBe(1);
    Bus::assertNothingBatched();
});

it('score vencido (fora da janela) reconsulta', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');
    ParticipanteScore::create([
        'participante_id' => $p->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now()->subDays(45),
        'dados_consultados' => ['situacao_cadastral' => 'ATIVA', 'sintegra' => ['situacao' => 'Habilitada'], 'cnd_federal' => ['status' => 'Negativa']],
    ]);

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id]);

    expect($out['consultados'])->toBe(1);
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1);
});

it('CNPJ mascarado é ignorado, nunca vira consulta', function () {
    $user = User::factory()->create();
    // 00000958000105 = RAIZEN mascarada (prefixo 00000 + DV inválido).
    $mascarado = rcParticipante($user, '00000958000105', 'RAIZ***');
    $real = rcParticipante($user, '11444777000161');

    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$mascarado->id, $real->id]);

    expect($out['consultados'])->toBe(1)
        ->and($out['ignorados'])->toBe(1);
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1
        && $batch->jobs->first()->alvoId === $real->id);
});

it('dedup: mesmo fornecedor em várias notas (id repetido) = 1 consulta', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');

    // N notas do mesmo fornecedor entram como o mesmo participante_id repetido.
    $out = app(RegularidadeContraparteService::class)->investigar($user->id, [$p->id, $p->id, $p->id]);

    expect($out['consultados'])->toBe(1);
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1);
});

it('isola por usuário: participante de outro dono não é consultado', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $alheio = rcParticipante($outro, '11444777000161');

    $out = app(RegularidadeContraparteService::class)->investigar($dono->id, [$alheio->id]);

    expect($out['consultados'])->toBe(0);
    Bus::assertNothingBatched();
});

// Regressão (2026-07-13): a barra ficava em 0% a consulta inteira num lote de 1 documento —
// o progresso era emitido só ANTES da chamada, com (indice-1)/total = 0. Agora cada documento
// também fecha a sua fatia, e no clearance completo os docs ocupam só a 1ª metade da barra.
it('progresso: documento único sai de 0 e o completo reserva a 2ª metade pra contraparte', function () {
    $job = new App\Jobs\ProcessarClearanceJob(
        loteId: 1, chave: str_repeat('1', 44), tipoDocumento: 'nfe', userId: 1,
        tabId: 'tab-x', clienteId: null, custoCreditos: 5, indice: 1, total: 1,
    );
    // Default (básico): 1 doc concluído = 95% (o 100 vem do 'finalizado').
    $pct = fn ($j, $n) => (new ReflectionMethod($j, 'pct'))->invoke($j, $n);
    expect($pct($job, 0))->toBe(0)->and($pct($job, 1))->toBe(95);

    // Completo: os documentos param em 50% — a contraparte ocupa 50→95.
    $jobFull = new App\Jobs\ProcessarClearanceJob(
        loteId: 1, chave: str_repeat('1', 44), tipoDocumento: 'nfe', userId: 1,
        tabId: 'tab-x', clienteId: null, custoCreditos: 10, indice: 1, total: 1, pctSpan: 50,
    );
    expect($pct($jobFull, 1))->toBe(50);
});

// Etapas nomeadas (mesmo contrato do Consulta CNPJ): a tela recebe a trilha e monta o strip a
// partir dela. Sem isso o JS adivinhava e imprimia "Etapa N" ao finalizar.
it('trilha de etapas: 2 no básico, 5 no completo, e as chaves batem com consultas.fonte_etapa', function () {
    $basico = App\Services\Clearance\ClearanceEtapas::para('basico');
    $full = App\Services\Clearance\ClearanceEtapas::para('full');

    expect($basico)->toHaveCount(2)
        ->and(collect($basico)->pluck('chave')->all())->toBe(['inicializacao', 'documentos'])
        ->and($full)->toHaveCount(5)
        ->and(collect($full)->pluck('chave')->all())
        ->toBe(['inicializacao', 'documentos', 'cadastrais', 'certidoes_federais', 'certidoes_estaduais']);

    // As 3 fontes da contraparte precisam cair nas etapas 3/4/5 — é assim que o
    // ProcessarConsultaJob (motor da Consulta CNPJ) resolve a etapa de cada fonte.
    expect(config('consultas.fonte_etapa.cadastro'))->toBe('cadastrais')
        ->and(config('consultas.fonte_etapa.cnd_federal'))->toBe('certidoes_federais')
        ->and(config('consultas.fonte_etapa.sintegra'))->toBe('certidoes_estaduais');

    // O fim tem NOME — nunca um rótulo genérico.
    expect(App\Services\Clearance\ClearanceEtapas::ultima('full')['label'])->toBe('SINTEGRA')
        ->and(App\Services\Clearance\ClearanceEtapas::ultima('basico')['label'])->toBe('Consultando SEFAZ');
});

it('tela de resultado entrega a trilha (data-etapas) pro strip', function () {
    config(['clearance.full.habilitado' => true]);
    $user = User::factory()->create(['credits' => 1000]);
    $lote = rcLote($user); // tier=full

    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('data-etapas', false)
        ->assertSee('Cadastro da contraparte', false)
        ->assertSee('CND Federal', false)
        ->assertSee('SINTEGRA', false);
});

// O wrapper "resultado completo do CNPJ" só pode mostrar fonte que FOI consultada. Sem isso, um
// score reusado de uma Consulta CNPJ de plano maior traria placeholders de certidões que ESTE
// clearance não consultou (o presenter desenha placeholder pra fonte que o PLANO pedia e não veio).
it('wrapper do resultado: só exibe as fontes consultadas (sem placeholder de não-consultada)', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');

    // Plano pede 3 certidões; o dado só trouxe CND Federal (as outras falharam/não vieram).
    $plano = App\Models\MonitoramentoPlano::create([
        'codigo' => 'teste_grande', 'nome' => 'Teste', 'descricao' => 'Plano de teste', 'custo_creditos' => 10, 'ativo' => true,
        'consultas_incluidas' => ['situacao_cadastral', 'cnd_federal', 'cnd_estadual', 'crf_fgts'],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 10,
    ]);
    App\Models\ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $p->id, 'user_id' => $user->id,
        'status' => App\Models\ConsultaResultado::STATUS_SUCESSO, 'consultado_em' => now(),
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
            // cnd_estadual e crf_fgts NÃO vieram
        ],
    ]);

    $presenter = app(App\Services\Consultas\ResultadoDetalhePresenter::class);

    // Score Fiscal (default): mostra o que o plano pediu e falhou → placeholder.
    $default = collect($presenter->detalheDoParticipante($p)['blocos'])->pluck('chave');
    expect($default)->toContain('cnd_estadual')->toContain('crf_fgts');

    // Clearance: só o que foi consultado de fato E dentro do escopo do produto (3 fontes).
    $detalhe = $presenter->detalheDoParticipante(
        $p,
        somenteConsultadas: true,
        somenteFontes: ['cadastro', 'sintegra', 'cnd_federal'],
    );
    expect(collect($detalhe['blocos'])->pluck('chave'))
        ->toContain('cadastro')->toContain('cnd_federal')
        ->not->toContain('cnd_estadual')
        ->not->toContain('crf_fgts');
});

// O clearance reusa o cache: a última consulta da contraparte pode ser uma Consulta CNPJ de plano
// MAIOR, com dado real de EST/MUN/FGTS. O wrapper do clearance não pode exibi-las — ele
// consultou 3 fontes, não 6. (Reportado por Felipe: "ainda tá exibindo EST? MUN— FGTS?".)
it('wrapper do clearance não mostra fonte fora do produto, mesmo com dado real dela', function () {
    $user = User::factory()->create();
    $p = rcParticipante($user, '11444777000161');

    $plano = App\Models\MonitoramentoPlano::create([
        'codigo' => 'compliance_teste', 'nome' => 'Compliance', 'descricao' => 'Plano de teste',
        'custo_creditos' => 25, 'ativo' => true,
        'consultas_incluidas' => ['situacao_cadastral', 'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'sintegra'],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 25,
    ]);
    App\Models\ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $p->id, 'user_id' => $user->id,
        'status' => App\Models\ConsultaResultado::STATUS_SUCESSO, 'consultado_em' => now(),
        // TODAS com dado real — mesmo assim as fora do escopo do clearance não podem aparecer.
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
            'sintegra' => ['situacao' => 'Habilitada'],
            'cnd_estadual' => ['status' => 'Negativa'],
            'cnd_municipal' => ['status' => 'Negativa'],
            'crf_fgts' => ['status' => 'Regular'],
        ],
    ]);

    $presenter = app(App\Services\Consultas\ResultadoDetalhePresenter::class);
    $doClearance = $presenter->detalheDoParticipante(
        $p,
        somenteConsultadas: true,
        somenteFontes: ['cadastro', 'sintegra', 'cnd_federal'],
    );

    expect(collect($doClearance['blocos'])->pluck('chave')->all())
        ->toBe(['cadastro', 'cnd_federal', 'sintegra']);
    // Os chips (EST, MUN, FGTS) também somem — era exatamente o que aparecia na tela.
    expect(collect($doClearance['certidoes'])->pluck('sigla')->all())
        ->toBe(['FED', 'SINT']);
});

// ── Integração pelo lote de clearance (resolução por snapshot + billing per-CNPJ) ──

function rcSnapshotEntrada(User $u, ConsultaLote $lote, string $chave, string $emitCnpj, string $destCnpj): void
{
    NfeConsulta::create([
        'user_id' => $u->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA',
        'emit_nome' => 'FORNECEDOR X', 'emit_cnpj' => $emitCnpj,
        'dest_nome' => 'PROPRIA', 'dest_cnpj' => $destCnpj,
    ]);
}

function rcLote(User $u, string $tier = 'full'): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $u->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 0,
        // O bloco de regularidade só aparece em lote CONTRATADO como completo.
        'resultado_resumo' => ['tier' => $tier],
    ]);
}

it('investigarPorLoteClearance resolve a contraparte pelo snapshot (sem cobrar aqui)', function () {
    $user = User::factory()->create(['credits' => 1000]);
    // Empresa própria = Cliente (dest da entrada), NÃO participante.
    Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
    $forn = rcParticipante($user, '11444777000161', 'FORNECEDOR X');
    $lote = rcLote($user);
    rcSnapshotEntrada($user, $lote, str_repeat('1', 44), '11444777000161', '00000000000191');
    $saldoAntes = $user->fresh()->credits;

    $out = app(RegularidadeContraparteService::class)->investigarPorLoteClearance($lote->id, $user->id);

    expect($out['consultados'])->toBe(1);
    // Só a contraparte (fornecedor) entra — a empresa própria (Cliente) nunca.
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1
        && $batch->jobs->first()->alvoId === $forn->id);
    // Preço FECHADO por nota: o débito acontece ao iniciar o lote de clearance, não aqui.
    expect($user->fresh()->credits)->toBe($saldoAntes);
});

// Regressão real (pega em render, 2026-07-13): a empresa própria costuma ter cadastro em
// `clientes` E em `participantes` (ver ValidacaoContabilService::participanteDaEmpresaPropriaId).
// Sem este filtro ela virava "contraparte": aparecia no bloco e era COBRADA como CNPJ novo.
it('empresa própria (cliente que também é participante) nunca vira contraparte nem cobra', function () {
    config(['clearance.full.habilitado' => true]);
    $user = User::factory()->create(['credits' => 1000]);
    $propriaDoc = '97551165000193';
    Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => $propriaDoc, 'razao_social' => 'HIDRATOP (propria)',
    ]);
    // A MESMA empresa também existe como participante (origem EFD, cenário real de prod).
    $propriaComoParticipante = rcParticipante($user, $propriaDoc, 'HIDRATOP (participante)');
    $forn = rcParticipante($user, '11444777000161', 'FORNECEDOR X');

    $lote = rcLote($user);
    rcSnapshotEntrada($user, $lote, str_repeat('8', 44), '11444777000161', $propriaDoc);
    $saldoAntes = $user->fresh()->credits;

    $out = app(RegularidadeContraparteService::class)->investigarPorLoteClearance($lote->id, $user->id);

    // Só o fornecedor — a própria empresa fica de fora do batch.
    expect($out['consultados'])->toBe(1);
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1
        && $batch->jobs->first()->alvoId === $forn->id);

    // E não aparece na tela como contraparte.
    $resumo = app(RegularidadeContraparteService::class)->resumoPorLoteClearance($lote->id, $user->id);
    expect(collect($resumo['contrapartes'])->pluck('participante_id')->all())
        ->toBe([$forn->id])
        ->not->toContain($propriaComoParticipante->id);
});

it('resultado do lote mostra a regularidade e cruza AUTORIZADA × contraparte irregular', function () {
    config(['clearance.full.habilitado' => true]);
    $user = User::factory()->create(['credits' => 1000]);
    $forn = rcParticipante($user, '11444777000161', 'FORNECEDOR PODRE LTDA');
    ParticipanteScore::create([
        'participante_id' => $forn->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now(), 'classificacao' => 'alto',
        'score_cnd_federal' => 80, // subscore > 0 = CND positiva (irregular)
        'dados_consultados' => [
            'situacao_cadastral' => 'BAIXADA',
            'sintegra' => ['situacao' => 'Inapta'],
            'cnd_federal' => ['status' => 'Positiva'],
        ],
    ]);
    $lote = rcLote($user);
    rcSnapshotEntrada($user, $lote, str_repeat('4', 44), '11444777000161', '00000000000191');

    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('Regularidade da contraparte', false)
        ->assertSee('FORNECEDOR PODRE LTDA', false)
        ->assertSee('Situação cadastral: BAIXADA', false)
        ->assertSee('CND Federal positiva', false)
        // O cruzamento que só o clearance enxerga.
        ->assertSee('crédito fiscal exposto a glosa', false)
        ->assertSee('1 irregular', false);
});

it('contraparte regular → bloco aparece sem alerta', function () {
    config(['clearance.full.habilitado' => true]);
    $user = User::factory()->create(['credits' => 1000]);
    $forn = rcParticipante($user, '11444777000161', 'FORNECEDOR OK LTDA');
    ParticipanteScore::create([
        'participante_id' => $forn->id, 'user_id' => $user->id,
        'ultima_consulta_em' => now(), 'classificacao' => 'baixo',
        'dados_consultados' => [
            'situacao_cadastral' => 'ATIVA',
            'sintegra' => ['situacao' => 'Habilitada'],
            'cnd_federal' => ['status' => 'Negativa'],
        ],
    ]);
    $lote = rcLote($user);
    rcSnapshotEntrada($user, $lote, str_repeat('6', 44), '11444777000161', '00000000000191');

    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('Nenhuma irregularidade', false)
        ->assertDontSee('crédito fiscal exposto a glosa', false);
});

// Lote BÁSICO não dispara regularidade — mostrar contraparte "em apuração" ali seria mentira
// (nada vem responder). O bloco só existe em lote contratado como completo.
it('lote básico → bloco de regularidade não aparece (não promete o que não vem)', function () {
    config(['clearance.full.habilitado' => true]);
    $user = User::factory()->create(['credits' => 1000]);
    rcParticipante($user, '11444777000161', 'FORNECEDOR X');
    $lote = rcLote($user, 'basico');
    rcSnapshotEntrada($user, $lote, str_repeat('5', 44), '11444777000161', '00000000000191');

    // (O bloco inteiro some — logo o "em apuração" dele também. Não dá pra afirmar sobre a string
    // "Consulta em andamento" solta: ela também é usada pelo texto de progresso da página.)
    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertDontSee('Regularidade da contraparte', false);
});

it('Full OFF → bloco de regularidade não aparece', function () {
    config(['clearance.full.habilitado' => false]);
    $user = User::factory()->create(['credits' => 1000]);
    $forn = rcParticipante($user, '11444777000161');
    ParticipanteScore::create([
        'participante_id' => $forn->id, 'user_id' => $user->id, 'ultima_consulta_em' => now(),
        'dados_consultados' => ['situacao_cadastral' => 'ATIVA'],
    ]);
    $lote = rcLote($user);
    rcSnapshotEntrada($user, $lote, str_repeat('7', 44), '11444777000161', '00000000000191');

    actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertDontSee('Regularidade da contraparte', false);
});
