<?php

use App\Actions\MercadoPago\CobrarRecargaMercadoPago;
use App\Actions\MercadoPago\ProcessarPagamentoMercadoPago;
use App\Actions\MercadoPago\RegistrarCobrancaAssinatura;
use App\Models\AccountSubscription;
use App\Models\MercadoPagoPayment;
use App\Models\RecargaAutomatica;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\AssinaturaPagamentoFalhouNotification;
use App\Notifications\AssinaturaRenovadaNotification;
use App\Notifications\CompraConfirmadaNotification;
use App\Notifications\RecargaAutomaticaConfirmadaNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.mercadopago.access_token' => 'TEST-token',
        'services.mercadopago.base_url' => 'https://api.mercadopago.com',
    ]);
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
    Notification::fake();
});

it('compra avulsa aprovada envia CompraConfirmada 1x (idempotente)', function () {
    Http::fake(['api.mercadopago.com/v1/payments/PAY-1' => Http::response([
        'status' => 'approved', 'status_detail' => 'accredited', 'payment_method_id' => 'pix',
    ], 200)]);

    $user = User::factory()->create();
    $pay = MercadoPagoPayment::create([
        'user_id' => $user->id, 'tipo' => 'purchase', 'pacote' => 'starter',
        'mp_payment_id' => 'PAY-1', 'status' => 'pending', 'valor' => 50.0, 'creditos' => 250,
        'idempotency_key' => 'k-pay-1',
    ]);

    app(ProcessarPagamentoMercadoPago::class)->execute('PAY-1');
    app(ProcessarPagamentoMercadoPago::class)->execute('PAY-1'); // re-entrega

    Notification::assertSentToTimes($user, CompraConfirmadaNotification::class, 1);
    expect($pay->fresh()->credited_at)->not->toBeNull();
});

it('auto top-up por saldo aprovado envia RecargaAutomaticaConfirmada', function () {
    Http::fake(['api.mercadopago.com/v1/payments/PAY-2' => Http::response([
        'status' => 'approved', 'status_detail' => 'accredited',
    ], 200)]);

    $user = User::factory()->create();
    RecargaAutomatica::create([
        'user_id' => $user->id, 'pacote' => 'starter', 'creditos' => 250, 'valor' => 50.0,
        'status' => RecargaAutomatica::STATUS_PENDENTE, 'gatilho' => RecargaAutomatica::GATILHO_SALDO,
        'cobranca_em_andamento' => true,
    ]);
    MercadoPagoPayment::create([
        'user_id' => $user->id, 'tipo' => 'auto_topup', 'pacote' => 'starter',
        'mp_payment_id' => 'PAY-2', 'status' => 'pending', 'valor' => 50.0, 'creditos' => 250,
        'idempotency_key' => 'k-pay-2',
    ]);

    app(ProcessarPagamentoMercadoPago::class)->execute('PAY-2');

    Notification::assertSentToTimes($user, RecargaAutomaticaConfirmadaNotification::class, 1);
    Notification::assertNotSentTo($user, CompraConfirmadaNotification::class);
});

it('cobranca de assinatura aprovada envia AssinaturaRenovada', function () {
    Http::fake(['api.mercadopago.com/authorized_payments/AP-1' => Http::response([
        'status' => 'approved', 'preapproval_id' => 'PRE-1', 'transaction_amount' => 149.0,
    ], 200)]);

    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal', 'mp_preapproval_id' => 'PRE-1',
    ]);

    app(RegistrarCobrancaAssinatura::class)->execute('AP-1');

    Notification::assertSentTo($user, AssinaturaRenovadaNotification::class);
    Notification::assertNotSentTo($user, AssinaturaPagamentoFalhouNotification::class);
});

it('cobranca de assinatura recusada envia AssinaturaPagamentoFalhou (dunning)', function () {
    Http::fake(['api.mercadopago.com/authorized_payments/AP-2' => Http::response([
        'status' => 'rejected', 'preapproval_id' => 'PRE-2', 'transaction_amount' => 149.0,
    ], 200)]);

    $plan = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal', 'mp_preapproval_id' => 'PRE-2',
    ]);

    app(RegistrarCobrancaAssinatura::class)->execute('AP-2');

    Notification::assertSentTo($user, AssinaturaPagamentoFalhouNotification::class);
    expect($sub->fresh()->status)->toBe(AccountSubscription::STATUS_INADIMPLENTE);
});

it('recarga automatica por tempo aprovada envia RecargaAutomaticaConfirmada 1x', function () {
    Http::fake(['api.mercadopago.com/authorized_payments/AP-3' => Http::response([
        'status' => 'approved', 'preapproval_id' => 'PRE-R3',
    ], 200)]);

    $user = User::factory()->create();
    RecargaAutomatica::create([
        'user_id' => $user->id, 'pacote' => 'business', 'creditos' => 1000, 'valor' => 200.0,
        'status' => RecargaAutomatica::STATUS_ATIVA, 'gatilho' => RecargaAutomatica::GATILHO_TEMPO,
        'mp_preapproval_id' => 'PRE-R3',
    ]);

    app(CobrarRecargaMercadoPago::class)->execute('AP-3');
    app(CobrarRecargaMercadoPago::class)->execute('AP-3'); // re-entrega

    Notification::assertSentToTimes($user, RecargaAutomaticaConfirmadaNotification::class, 1);
});

it('recarga automatica por tempo RECUSADA envia RecargaAutomaticaPausada 1x (idempotente)', function () {
    Mail::fake();
    Http::fake(['api.mercadopago.com/authorized_payments/AP-4' => Http::response([
        'status' => 'rejected', 'preapproval_id' => 'PRE-R4',
    ], 200)]);

    $user = User::factory()->create();
    RecargaAutomatica::create([
        'user_id' => $user->id, 'pacote' => 'business', 'creditos' => 1000, 'valor' => 200.0,
        'status' => RecargaAutomatica::STATUS_ATIVA, 'gatilho' => RecargaAutomatica::GATILHO_TEMPO,
        'mp_preapproval_id' => 'PRE-R4',
    ]);

    app(CobrarRecargaMercadoPago::class)->execute('AP-4');
    app(CobrarRecargaMercadoPago::class)->execute('AP-4'); // re-entrega não reenvia

    Mail::assertQueued(\App\Mail\RecargaAutomaticaPausada::class, 1);
    expect(RecargaAutomatica::where('user_id', $user->id)->first()->status)
        ->toBe(RecargaAutomatica::STATUS_INADIMPLENTE);
});
