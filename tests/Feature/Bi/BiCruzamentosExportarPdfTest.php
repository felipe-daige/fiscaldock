<?php

use App\Models\AccountSubscription;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\RiskScoreService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

function cruzamentosPdfComPlano(User $user, string $codigo): User
{
    $plano = SubscriptionPlan::where('codigo', $codigo)->first();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal',
    ]);

    return $user;
}

function seedCruzamentoParaPdf(User $user): void
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $forn = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Fornecedor Devedor SA',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 10, 'tab_id' => 'tab-pdf', 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $forn->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Positiva']], 'consultado_em' => now(),
    ]);
    app(RiskScoreService::class)->atualizarScore($forn, ['cnd_federal' => ['status' => 'Positiva']]);
    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $forn->id,
        'importacao_id' => $imp->id, 'chave_acesso' => '35240000000000000000000000000000000000040001',
        'modelo' => '55', 'numero' => 40001, 'serie' => '0', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada', 'valor_total' => 1500.00, 'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);
}

it('Free puro recebe 403 no PDF de cruzamentos (gate bi_completo)', function () {
    actingAs(User::factory()->create())
        ->get('/app/bi/cruzamentos/exportar-pdf')
        ->assertStatus(403);
});

it('plano pago baixa o PDF de cruzamentos', function () {
    $user = cruzamentosPdfComPlano(User::factory()->create(), 'essencial');
    seedCruzamentoParaPdf($user);

    $resp = actingAs($user)->get('/app/bi/cruzamentos/exportar-pdf');

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect($resp->headers->get('content-disposition'))->toContain('cruzamentos-fiscais-');
});

it('trial ativo baixa o PDF de cruzamentos (trial libera tudo)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedCruzamentoParaPdf($user);

    actingAs($user)->get('/app/bi/cruzamentos/exportar-pdf')->assertOk();
});

it('anexa o cookie bi_download quando recebe download_token (overlay do frontend)', function () {
    $user = cruzamentosPdfComPlano(User::factory()->create(), 'essencial');
    seedCruzamentoParaPdf($user);

    $resp = actingAs($user)->get('/app/bi/cruzamentos/exportar-pdf?download_token=abc123');

    $nomes = array_map(fn ($c) => $c->getName(), $resp->headers->getCookies());
    expect($nomes)->toContain('bi_download');
});

it('a tela de cruzamentos mostra o botão de exportar pro plano pago', function () {
    $user = cruzamentosPdfComPlano(User::factory()->create(), 'essencial');

    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('modal-exportar-cruzamentos', false)
        ->assertSee('Exportar cruzamentos', false);
});
