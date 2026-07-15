<?php

use App\Models\AccountSubscription;
use App\Models\SaldoTransacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.mercadopago.access_token' => 'TEST-token',
        'services.mercadopago.public_key' => 'TEST-pk',
        'services.mercadopago.webhook_secret' => 'webhook-secret-xyz',
        'services.mercadopago.base_url' => 'https://api.mercadopago.com',
    ]);
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

/**
 * Monta o header x-signature HMAC do webhook (idêntico ao de PagamentoMercadoPagoTest).
 */
function assinaturaValidaSub(string $dataId, string $requestId, string $secret): string
{
    $ts = (string) time();
    $manifest = 'id:'.strtolower($dataId).';request-id:'.$requestId.';ts:'.$ts.';';
    $v1 = hash_hmac('sha256', $manifest, $secret);

    return "ts={$ts},v1={$v1}";
}

it('persiste as colunas novas de assinatura', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $plan->update(['mp_preapproval_plan_id_mensal' => 'PLAN-MES-1']);

    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_PENDENTE,
        'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-1',
    ]);

    expect($sub->fresh()->status)->toBe('pendente');
    expect($plan->fresh()->mpPlanId('mensal'))->toBe('PLAN-MES-1');
    expect($plan->precoCentavos('anual'))->toBe(99000);
});

it('client cria preapproval_plan, preapproval, consulta e cancela', function () {
    Http::fake([
        'api.mercadopago.com/preapproval_plan' => Http::response(['id' => 'PLAN-1'], 201),
        'api.mercadopago.com/preapproval/PRE-1' => Http::response(['id' => 'PRE-1', 'status' => 'authorized'], 200),
        'api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-1', 'status' => 'authorized'], 201),
        'api.mercadopago.com/authorized_payments/AP-1' => Http::response(['id' => 'AP-1', 'status' => 'approved'], 200),
    ]);

    $client = new App\Services\MercadoPago\MercadoPagoClient;

    expect($client->criarPreapprovalPlan(['reason' => 'x'])['id'])->toBe('PLAN-1');
    expect($client->criarPreapproval(['preapproval_plan_id' => 'PLAN-1'])['status'])->toBe('authorized');
    expect($client->buscarPreapproval('PRE-1')['status'])->toBe('authorized');
    expect($client->cancelarPreapproval('PRE-1')['id'])->toBe('PRE-1'); // PUT status cancelled
    expect($client->buscarAuthorizedPayment('AP-1')['status'])->toBe('approved');
});

it('sincroniza preapproval_plans dos tiers pagos e grava os ids', function () {
    Http::fake([
        'api.mercadopago.com/preapproval_plan' => Http::sequence()
            ->push(['id' => 'PLAN-ESS-MES'], 201)
            ->push(['id' => 'PLAN-ESS-ANO'], 201)
            ->push(['id' => 'PLAN-PRO-MES'], 201)
            ->push(['id' => 'PLAN-PRO-ANO'], 201)
            ->push(['id' => 'PLAN-ESC-MES'], 201)
            ->push(['id' => 'PLAN-ESC-ANO'], 201),
    ]);

    $this->artisan('assinatura:sincronizar-planos')->assertExitCode(0);

    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();
    expect($ess->mp_preapproval_plan_id_mensal)->toBe('PLAN-ESS-MES');
    expect($ess->mp_preapproval_plan_id_anual)->toBe('PLAN-ESS-ANO');

    // Free e Enterprise (preço 0 / sob consulta) NÃO geram preapproval_plan.
    expect(SubscriptionPlan::where('codigo', 'free')->first()->mp_preapproval_plan_id_mensal)->toBeNull();
    expect(SubscriptionPlan::where('codigo', 'enterprise')->first()->mp_preapproval_plan_id_mensal)->toBeNull();

    // Escritório mensal (R$599) sincroniza; anual (R$5.990 > teto MP) é PULADO → fica nulo (vai pro WhatsApp).
    $esc = SubscriptionPlan::where('codigo', 'escritorio')->first();
    expect($esc->mp_preapproval_plan_id_mensal)->toBe('PLAN-ESC-MES');
    expect($esc->mp_preapproval_plan_id_anual)->toBeNull();
});

it('assinar cria preapproval com preço do backend e persiste subscription pendente', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);

    Http::fake([
        'api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-NEW', 'status' => 'pending'], 201),
    ]);

    $user = User::factory()->create();
    actingAs($user);

    $resp = postJson(route('app.assinatura.criar'), [
        'plano' => 'essencial',
        'ciclo' => 'mensal',
        'token' => 'card-token-xyz',     // do Brick
        'amount' => 1.00,                 // tentativa de adulteração — ignorada
    ])->assertOk();

    $resp->assertJsonPath('status', 'pendente');

    $sub = AccountSubscription::first();
    expect($sub->status)->toBe('pendente');
    expect($sub->mp_preapproval_id)->toBe('PRE-NEW');
    expect($sub->ciclo)->toBe('mensal');

    // valor enviado ao MP = catálogo (99.00), não o 1.00 do front.
    Http::assertSent(fn ($req) => $req->url() === 'https://api.mercadopago.com/preapproval'
        && $req['auto_recurring']['transaction_amount'] === 99.0
        && $req['card_token_id'] === 'card-token-xyz'
        && $req['preapproval_plan_id'] === 'PLAN-ESS-MES');
});

it('re-assinar após cancelar reusa a linha (user_id é unique, não estoura)', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    SubscriptionPlan::where('codigo', 'profissional')->update(['mp_preapproval_plan_id_anual' => 'PLAN-PRO-ANO']);

    Http::fake([
        'api.mercadopago.com/preapproval' => Http::sequence()
            ->push(['id' => 'PRE-A', 'status' => 'authorized'], 201)
            ->push(['id' => 'PRE-B', 'status' => 'authorized'], 201),
    ]);

    $user = User::factory()->create();
    actingAs($user);

    postJson(route('app.assinatura.criar'), ['plano' => 'essencial', 'ciclo' => 'mensal', 'token' => 't1'])->assertOk();
    // cancela manualmente (simula estado pós-cancelamento) e re-assina outro tier/ciclo
    AccountSubscription::where('user_id', $user->id)->update(['status' => 'cancelada']);
    postJson(route('app.assinatura.criar'), ['plano' => 'profissional', 'ciclo' => 'anual', 'token' => 't2'])->assertOk();

    expect(AccountSubscription::where('user_id', $user->id)->count())->toBe(1); // reusou a linha
    $sub = AccountSubscription::where('user_id', $user->id)->first();
    expect($sub->status)->toBe('pendente');
    expect($sub->ciclo)->toBe('anual');
    expect($sub->mp_preapproval_id)->toBe('PRE-B');
});

it('assinar recusa cobrança acima do teto do MP (R$4k) → checkout assistido', function () {
    // Escritório com os dois planos sincronizados; anual (R$5.990) > teto 4000, mensal (R$599) ok.
    SubscriptionPlan::where('codigo', 'escritorio')->update([
        'mp_preapproval_plan_id_anual' => 'PLAN-ESC-ANO',
        'mp_preapproval_plan_id_mensal' => 'PLAN-ESC-MES',
    ]);

    Http::fake(['api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-MES', 'status' => 'authorized'], 201)]);

    $user = User::factory()->create();
    actingAs($user);

    // Anual: estoura o teto → 422 ANTES de chamar o MP.
    postJson(route('app.assinatura.criar'), ['plano' => 'escritorio', 'ciclo' => 'anual', 'token' => 't'])
        ->assertStatus(422)
        ->assertJsonPath('error', 'Valor acima do limite de cobrança automática. Fale com o atendimento para assinar este plano.');

    // Mensal (R$599) está abaixo do teto e segue normal.
    postJson(route('app.assinatura.criar'), ['plano' => 'escritorio', 'ciclo' => 'mensal', 'token' => 't'])->assertOk();
    expect(AccountSubscription::where('user_id', $user->id)->first()->mp_preapproval_id)->toBe('PRE-MES');

    // O teto barrou o anual ANTES do MP: nenhuma chamada ao preapproval pro ciclo anual.
    Http::assertSentCount(1); // só a do mensal
});

it('assinar recusa plano grátis/enterprise/sem id de plano', function () {
    $user = User::factory()->create();
    actingAs($user);
    Http::fake();

    postJson(route('app.assinatura.criar'), ['plano' => 'free', 'ciclo' => 'mensal', 'token' => 't'])->assertStatus(422);
    postJson(route('app.assinatura.criar'), ['plano' => 'enterprise', 'ciclo' => 'mensal', 'token' => 't'])->assertStatus(422);
    // essencial sem preapproval_plan sincronizado também é recusado
    postJson(route('app.assinatura.criar'), ['plano' => 'essencial', 'ciclo' => 'mensal', 'token' => 't'])->assertStatus(422);

    // A Action lança ANTES de criar a linha → nenhuma assinatura persistida.
    expect(AccountSubscription::count())->toBe(0);
    Http::assertNothingSent();
});

it('webhook preapproval authorized ativa a assinatura e concede o 1º mês como purchase', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create(['credits' => 0]);
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_PENDENTE, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-AUT', 'creditos_inclusos_saldo' => 0,
    ]);

    Http::fake([
        'api.mercadopago.com/preapproval/PRE-AUT' => Http::response([
            'id' => 'PRE-AUT', 'status' => 'authorized', 'external_reference' => (string) $sub->id,
        ], 200),
    ]);

    $sig = assinaturaValidaSub('PRE-AUT', 'req-a', 'webhook-secret-xyz');
    $enviar = fn () => $this->withHeaders(['x-signature' => $sig, 'x-request-id' => 'req-a'])
        ->postJson('/api/mercado-pago/webhook?type=subscription_preapproval&data_id=PRE-AUT', [
            'type' => 'subscription_preapproval', 'data' => ['id' => 'PRE-AUT'],
        ]);

    $enviar()->assertOk();
    $enviar()->assertOk(); // reentrega não concede de novo

    $user->refresh();
    expect($user->credits)->toBe(35.0);
    expect($sub->fresh()->status)->toBe('ativa');
    expect(SaldoTransacao::where('type', 'purchase')->count())->toBe(1); // destrava 1ª compra
});

it('webhook preapproval cancelled marca a assinatura cancelada (sem apagar saldo)', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create(['credits' => 300]);
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-CAN', 'creditos_inclusos_saldo' => 300,
    ]);

    Http::fake([
        'api.mercadopago.com/preapproval/PRE-CAN' => Http::response([
            'id' => 'PRE-CAN', 'status' => 'cancelled', 'external_reference' => (string) $sub->id,
        ], 200),
    ]);

    $sig = assinaturaValidaSub('PRE-CAN', 'req-c', 'webhook-secret-xyz');
    $this->withHeaders(['x-signature' => $sig, 'x-request-id' => 'req-c'])
        ->postJson('/api/mercado-pago/webhook?type=subscription_preapproval&data_id=PRE-CAN', [
            'type' => 'subscription_preapproval', 'data' => ['id' => 'PRE-CAN'],
        ])->assertOk();

    expect($sub->fresh()->status)->toBe('cancelada');
    expect($user->fresh()->credits)->toBe(300.0); // saldo preservado (guardrail)
});

it('webhook authorized_payment approved registra a cobrança e NÃO concede saldo', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create(['credits' => 300]);
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-PAY', 'creditos_inclusos_saldo' => 300,
    ]);

    Http::fake([
        'api.mercadopago.com/authorized_payments/AP-9' => Http::response([
            'id' => 'AP-9', 'status' => 'approved', 'preapproval_id' => 'PRE-PAY', 'transaction_amount' => 99.0,
        ], 200),
    ]);

    $sig = assinaturaValidaSub('AP-9', 'req-p', 'webhook-secret-xyz');
    $this->withHeaders(['x-signature' => $sig, 'x-request-id' => 'req-p'])
        ->postJson('/api/mercado-pago/webhook?type=subscription_authorized_payment&data_id=AP-9', [
            'type' => 'subscription_authorized_payment', 'data' => ['id' => 'AP-9'],
        ])->assertOk();

    expect($user->fresh()->credits)->toBe(300.0); // concessão é do scheduler
    expect(App\Models\MercadoPagoPayment::where('tipo', 'subscription')->where('account_subscription_id', $sub->id)->count())->toBe(1);
});

it('webhook authorized_payment rejected marca inadimplente', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-REJ',
    ]);

    Http::fake([
        'api.mercadopago.com/authorized_payments/AP-R' => Http::response([
            'id' => 'AP-R', 'status' => 'rejected', 'preapproval_id' => 'PRE-REJ',
        ], 200),
    ]);

    $sig = assinaturaValidaSub('AP-R', 'req-r', 'webhook-secret-xyz');
    $this->withHeaders(['x-signature' => $sig, 'x-request-id' => 'req-r'])
        ->postJson('/api/mercado-pago/webhook?type=subscription_authorized_payment&data_id=AP-R', [
            'type' => 'subscription_authorized_payment', 'data' => ['id' => 'AP-R'],
        ])->assertOk();

    expect($sub->fresh()->status)->toBe('inadimplente');
});

it('scheduler concede saldo só de assinaturas ativas com proximo_grant vencido (idempotente)', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();

    $userVencido = User::factory()->create(['credits' => 0]);
    AccountSubscription::create([
        'user_id' => $userVencido->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0, 'proximo_grant_em' => now()->subDay(),
    ]);

    $userFuturo = User::factory()->create(['credits' => 0]);
    AccountSubscription::create([
        'user_id' => $userFuturo->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0, 'proximo_grant_em' => now()->addDays(10),
    ]);

    $userInadimplente = User::factory()->create(['credits' => 0]);
    AccountSubscription::create([
        'user_id' => $userInadimplente->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_INADIMPLENTE, 'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0, 'proximo_grant_em' => now()->subDay(),
    ]);

    $this->artisan('assinatura:conceder-saldo')->assertExitCode(0);

    expect($userVencido->fresh()->credits)->toBe(35.0);     // concedeu
    expect($userFuturo->fresh()->credits)->toBe(0.0);        // ainda não vence
    expect($userInadimplente->fresh()->credits)->toBe(0.0);  // não ativa

    // Re-rodar não concede de novo (proximo_grant avançou pro futuro).
    $this->artisan('assinatura:conceder-saldo')->assertExitCode(0);
    expect($userVencido->fresh()->credits)->toBe(35.0);
    expect(SaldoTransacao::where('type', 'subscription_credit')->count())->toBe(1);
});

it('cancelar chama o MP e marca a assinatura cancelada (mantém saldo até o fim do ciclo)', function () {
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create(['credits' => 300]);
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-DEL', 'creditos_inclusos_saldo' => 300,
    ]);

    Http::fake(['api.mercadopago.com/preapproval/PRE-DEL' => Http::response(['id' => 'PRE-DEL', 'status' => 'cancelled'], 200)]);

    actingAs($user);
    postJson(route('app.assinatura.cancelar'))->assertOk();

    expect($sub->fresh()->status)->toBe('cancelada');
    expect($user->fresh()->credits)->toBe(300.0); // saldo preservado
    Http::assertSent(fn ($req) => $req->method() === 'PUT' && str_ends_with($req->url(), '/preapproval/PRE-DEL'));
});

it('cancelar sem assinatura ativa retorna 422', function () {
    $user = User::factory()->create();
    actingAs($user);
    Http::fake();
    postJson(route('app.assinatura.cancelar'))->assertStatus(422);
    Http::assertNothingSent();
});

it('a página /app/planos renderiza o gatilho de assinar com o SDK do MP e a public key', function () {
    config(['services.mercadopago.public_key' => 'TEST-PK-PLANOS']);

    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('sdk.mercadopago.com/js/v2');
    expect($html)->toContain('TEST-PK-PLANOS');
    expect($html)->toContain('data-assinar');
    expect($html)->toContain('/js/assinatura.js');
    // a UI antiga de "em breve" não pode mais existir
    expect($html)->not->toContain('Assinar — em breve');
    // teto do MP + WhatsApp expostos pro front decidir checkout assistido
    expect($html)->toContain('__MP_TETO_CENTAVOS');
    expect($html)->toContain('__WHATSAPP_URL');
    expect($html)->toContain('5567999844366'); // número do WhatsApp (barras escapadas pelo @json)
});

it('trocar de plano cria a nova preapproval e cancela a antiga no MP (upgrade)', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    SubscriptionPlan::where('codigo', 'profissional')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-PRO-MES']);

    $essencial = SubscriptionPlan::where('codigo', 'essencial')->first();

    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $essencial->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-OLD',
        'creditos_inclusos_saldo' => 300,
    ]);
    actingAs($user);

    Http::fake([
        'api.mercadopago.com/preapproval/PRE-OLD' => Http::response(['id' => 'PRE-OLD', 'status' => 'cancelled'], 200),
        'api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-NEW', 'status' => 'pending'], 201),
    ]);

    postJson(route('app.assinatura.trocar'), [
        'plano' => 'profissional', 'ciclo' => 'mensal', 'token' => 'card-token-up',
    ])->assertOk()->assertJsonPath('status', 'pendente');

    // reusa a linha, agora apontando pro tier destino + nova preapproval
    expect(AccountSubscription::where('user_id', $user->id)->count())->toBe(1);
    $sub->refresh();
    expect($sub->subscription_plan_id)->toBe(SubscriptionPlan::where('codigo', 'profissional')->first()->id);
    expect($sub->mp_preapproval_id)->toBe('PRE-NEW');

    // criou a nova (POST) e cancelou a antiga (PUT no id antigo)
    Http::assertSent(fn ($req) => $req->url() === 'https://api.mercadopago.com/preapproval'
        && $req['preapproval_plan_id'] === 'PLAN-PRO-MES');
    Http::assertSent(fn ($req) => $req->method() === 'PUT'
        && $req->url() === 'https://api.mercadopago.com/preapproval/PRE-OLD');
});

it('trocar pro mesmo plano e ciclo é recusado sem tocar no MP', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    $essencial = SubscriptionPlan::where('codigo', 'essencial')->first();

    $user = User::factory()->create();
    AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $essencial->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-SAME',
    ]);
    actingAs($user);
    Http::fake();

    postJson(route('app.assinatura.trocar'), [
        'plano' => 'essencial', 'ciclo' => 'mensal', 'token' => 't',
    ])->assertStatus(422)->assertJsonPath('error', 'Você já está neste plano e ciclo.');

    Http::assertNothingSent();
});

it('trocar sem assinatura viva age como assinatura nova', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);

    Http::fake(['api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-FIRST', 'status' => 'pending'], 201)]);

    $user = User::factory()->create();
    actingAs($user);

    postJson(route('app.assinatura.trocar'), [
        'plano' => 'essencial', 'ciclo' => 'mensal', 'token' => 't',
    ])->assertOk();

    expect(AccountSubscription::where('user_id', $user->id)->first()->mp_preapproval_id)->toBe('PRE-FIRST');
    // nenhuma preapproval antiga pra cancelar → só o POST de criação
    Http::assertSentCount(1);
});

it('a página /app/planos mostra CTAs de upgrade/downgrade e "Voltar pro Free" quando há assinatura ativa', function () {
    $profissional = SubscriptionPlan::where('codigo', 'profissional')->first();

    $user = User::factory()->create();
    AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $profissional->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-VIEW',
    ]);
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('data-modo="trocar"');
    expect($html)->toContain('Fazer upgrade');    // escritório (ordem > profissional)
    expect($html)->toContain('Fazer downgrade');  // essencial (ordem < profissional)
    expect($html)->toContain('data-cancelar');    // Free vira "Voltar para o Free"
    expect($html)->toContain('__TROCAR_URL');
});
