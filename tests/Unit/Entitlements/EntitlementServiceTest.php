<?php

use App\Models\AccountSubscription;
use App\Models\Cliente;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
    $this->svc = new EntitlementService;
});

function assinar(User $user, string $codigo, array $overrides = []): void
{
    $plano = SubscriptionPlan::where('codigo', $codigo)->first();
    AccountSubscription::create(array_merge([
        'user_id' => $user->id,
        'subscription_plan_id' => $plano->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ], $overrides));
}

it('sem assinatura resolve para o plano Free', function () {
    $user = User::factory()->create();
    expect($this->svc->planFor($user)->codigo)->toBe('free');
});

it('assinatura não ativa não libera plano pago', function () {
    $user = User::factory()->create();
    assinar($user, 'profissional', ['status' => AccountSubscription::STATUS_CANCELADA]);

    expect($this->svc->planFor($user)->codigo)->toBe('free');
    expect($this->svc->permits($user, 'clearance_lote'))->toBeFalse();
});

it('can() respeita capabilities booleanas do plano', function () {
    $user = User::factory()->create();
    assinar($user, 'essencial');

    expect($this->svc->can($user, 'clearance_lote'))->toBeTrue();
    expect($this->svc->can($user, 'clearance_full'))->toBeFalse();
});

it('Free não tem clearance em lote', function () {
    $user = User::factory()->create();
    expect($this->svc->can($user, 'clearance_lote'))->toBeFalse();
});

it('exportFormats e capability cru', function () {
    $user = User::factory()->create();
    assinar($user, 'profissional');

    expect($this->svc->exportFormats($user))->toBe(['csv', 'excel']);
    expect($this->svc->capability($user, 'bi'))->toBe('completo');
    expect($this->svc->capability($user, 'retencao_meses'))->toBeNull();
});

it('limit reflete a ausência de teto comercial nos tiers pagos', function () {
    $user = User::factory()->create();
    assinar($user, 'essencial');
    expect($this->svc->limit($user, 'limite_cnpjs_monitorados'))->toBeNull();

    $ent = User::factory()->create();
    assinar($ent, 'enterprise');
    expect($this->svc->limit($ent, 'limite_cnpjs_monitorados'))->toBeNull();
});

it('faixaFor reflete a faixa comprada pelo tier', function () {
    $user = User::factory()->create();
    assinar($user, 'escritorio');
    expect($this->svc->faixaFor($user))->toBe('y');
});

it('consumptionCap sem valor explícito não cria teto escondido', function () {
    $user = User::factory()->create();
    assinar($user, 'essencial');
    expect($this->svc->consumptionCap($user))->toBe(0);
});

it('consumptionCap respeita o limite explícito do cliente', function () {
    $user = User::factory()->create();
    assinar($user, 'essencial', ['limite_consumo_automatico' => 120]);
    expect($this->svc->consumptionCap($user))->toBe(120);
});

function ativarTrial(User $user, int $creditos = 50): void
{
    $user->forceFill([
        'trial_used' => true,
        'trial_started_at' => now(),
        'trial_expires_at' => now()->addDays(30),
        'trial_credits_remaining' => $creditos,
    ])->save();
}

it('permits(): Free sem trial é bloqueado nas capabilities pagas', function () {
    $user = User::factory()->create();
    expect($this->svc->permits($user, 'clearance_lote'))->toBeFalse();
    expect($this->svc->permits($user, 'score_historico'))->toBeFalse();
    expect($this->svc->permits($user, 'export'))->toBeFalse();
});

it('permits(): trial ativo libera tudo (mesmo no plano Free)', function () {
    $user = User::factory()->create();
    ativarTrial($user);
    expect($this->svc->permits($user, 'clearance_lote'))->toBeTrue();
    expect($this->svc->permits($user, 'score_historico'))->toBeTrue();
    expect($this->svc->permits($user, 'export'))->toBeTrue();
});

it('permits(): trial expirado NÃO libera (volta a valer o plano Free)', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'trial_used' => true,
        'trial_expires_at' => now()->subDay(),
        'trial_credits_remaining' => 50,
    ])->save();
    expect($this->svc->permits($user, 'clearance_lote'))->toBeFalse();
});

it('permits(): plano pago libera conforme a capability', function () {
    $user = User::factory()->create();
    assinar($user, 'essencial'); // clearance_lote=true, export=[csv], score_historico=false
    expect($this->svc->permits($user, 'clearance_lote'))->toBeTrue();
    expect($this->svc->permits($user, 'export'))->toBeTrue();
    expect($this->svc->permits($user, 'score_historico'))->toBeFalse();
});

// ---- cap de clientes (empresa própria + N do tier) ----

function criarCliente(User $user, string $doc, bool $propria = false): Cliente
{
    return Cliente::create([
        'user_id' => $user->id,
        'documento' => $doc,
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Empresa '.$doc,
        'is_empresa_propria' => $propria,
        'ativo' => true,
    ]);
}

it('limiteClientes não bloqueia cadastro em nenhum tier', function () {
    $free = User::factory()->create();
    expect($this->svc->limiteClientes($free))->toBeNull();

    $ess = User::factory()->create();
    assinar($ess, 'essencial');
    expect($this->svc->limiteClientes($ess))->toBeNull();

    $ent = User::factory()->create();
    assinar($ent, 'enterprise');
    expect($this->svc->limiteClientes($ent))->toBeNull();

    $trial = User::factory()->create();
    ativarTrial($trial);
    expect($this->svc->limiteClientes($trial))->toBeNull();
});

it('clientesAtuais ignora a empresa própria', function () {
    $user = User::factory()->create();
    criarCliente($user, '11111111000191', propria: true);
    criarCliente($user, '22222222000191');
    expect($this->svc->clientesAtuais($user))->toBe(1);
});

it('podeAdicionarCliente não trava o cadastro no Free', function () {
    $user = User::factory()->create();
    criarCliente($user, '11111111000191', propria: true);
    expect($this->svc->podeAdicionarCliente($user))->toBeTrue();

    criarCliente($user, '22222222000191');
    expect($this->svc->podeAdicionarCliente($user))->toBeTrue();
});

it('podeAdicionarCliente: trial ativo nunca trava', function () {
    $user = User::factory()->create();
    ativarTrial($user);
    foreach (['33333333000191', '44444444000191', '55555555000191'] as $doc) {
        criarCliente($user, $doc);
    }
    expect($this->svc->podeAdicionarCliente($user))->toBeTrue();
});

it('firstOrCreateClienteComCap preserva compatibilidade sem bloquear novos clientes', function () {
    $user = User::factory()->create();
    criarCliente($user, '11111111000191', propria: true);

    $c1 = $this->svc->firstOrCreateClienteComCap($user->id, '22222222000191', ['tipo_pessoa' => 'PJ', 'razao_social' => 'A', 'ativo' => true]);
    expect($c1)->not->toBeNull();
    expect($c1->is_empresa_propria)->toBeFalse();

    $c2 = $this->svc->firstOrCreateClienteComCap($user->id, '33333333000191', ['tipo_pessoa' => 'PJ']);
    expect($c2)->not->toBeNull();
    expect(Cliente::where('user_id', $user->id)->where('documento', '33333333000191')->exists())->toBeTrue();

    // vincular um existente sempre funciona (não conta como novo)
    $again = $this->svc->firstOrCreateClienteComCap($user->id, '22222222000191', []);
    expect($again->id)->toBe($c1->id);
});

it('firstOrCreateClienteComCap: trial cria à vontade', function () {
    $user = User::factory()->create();
    ativarTrial($user);
    foreach (['22222222000191', '33333333000191', '44444444000191'] as $d) {
        expect($this->svc->firstOrCreateClienteComCap($user->id, $d, ['tipo_pessoa' => 'PJ']))->not->toBeNull();
    }
});
