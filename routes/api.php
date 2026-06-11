<?php

use App\Http\Controllers\Api\DataReceiverController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

// ============================================
// Health Check (sem autenticação)
// ============================================
Route::get('/health', [DataReceiverController::class, 'health'])
    ->name('api.health');

// ============================================
// Importação EFD
// ============================================

// Recebe progresso de importação EFD (usado pelo n8n)
Route::post('/importacao/efd/progresso', [DataReceiverController::class, 'receiveImportacaoTxtProgress'])
    ->name('api.importacao.efd.progresso');

// Recebe progresso de extração de notas EFD por bloco (A, C, D)
// n8n envia fase + contadores por bloco; SSE lê e mescla no payload principal
Route::post('/importacao/efd/notas/progresso', [DataReceiverController::class, 'receiveNotasEfdProgress'])
    ->name('api.importacao.efd.notas.progresso');

// Recebe divergencia detectada pelo Auditor n8n (item descartado, duplicado, cancelado).
// Idempotente via (importacao_id, bloco, chave_acesso, numero_item, motivo).
Route::post('/importacao/efd/divergencia', [DataReceiverController::class, 'receiveEfdDivergencia'])
    ->name('api.importacao.efd.divergencia');

// Finaliza importação EFD: n8n chama 1x no fim, Laravel constrói resumo_final
// a partir do banco (single source of truth), persiste, atualiza cache SSE.
Route::post('/importacao/efd/finalizar', [DataReceiverController::class, 'finalizarImportacaoEfd'])
    ->name('api.importacao.efd.finalizar');

// ============================================
// Mercado Pago — webhook de pagamentos
// ============================================

// Sem auth de sessão: valida assinatura HMAC x-signature internamente.
// Nunca credita pelo corpo; consulta a API do MP e libera créditos idempotentemente.
Route::post('/mercado-pago/webhook', MercadoPagoWebhookController::class)
    ->name('api.mercadopago.webhook');

// Alias para o webhook de TESTE configurado no painel MP (URL com /teste/).
// Mesmo controller, mesma validação HMAC — atende test mode e produção.
Route::post('/teste/mercado-pago/webhook', MercadoPagoWebhookController::class)
    ->name('api.mercadopago.webhook.teste');
