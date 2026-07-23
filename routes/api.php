<?php

use App\Http\Controllers\Api\DataReceiverController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

// ============================================
// Health Check (sem autenticação)
// ============================================
Route::get('/health', [DataReceiverController::class, 'health'])
    ->name('api.health');

// A extração EFD roda 100% no Laravel (ProcessarEfdImportacaoJob) — os webhooks de entrada
// do n8n (progresso/notas/divergência/finalizar) foram removidos.

// ============================================
// Mercado Pago — webhook de pagamentos
// ============================================

// Sem auth de sessão: valida assinatura HMAC x-signature internamente.
// Nunca movimenta saldo pelo corpo; consulta a API do MP e libera saldo idempotentemente.
Route::post('/mercado-pago/webhook', MercadoPagoWebhookController::class)
    ->name('api.mercadopago.webhook');

// Alias para o webhook de TESTE configurado no painel MP (URL com /teste/).
// Mesmo controller, mesma validação HMAC — atende test mode e produção.
Route::post('/teste/mercado-pago/webhook', MercadoPagoWebhookController::class)
    ->name('api.mercadopago.webhook.teste');
