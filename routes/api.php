<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

// Rota de API para receber dados via HTTP - aceita token API ou sessão
// No Laravel 12, rotas em api.php recebem automaticamente o prefixo /api
Route::post('/data/receive', [DataReceiverController::class, 'receive'])
    ->name('api.data.receive');

// Rota para receber CSV em base64 do n8n
// Aceita autenticação via token API (para n8n) ou sessão
Route::post('/data/receive/raf/csvfile', [DataReceiverController::class, 'receiveCsv'])
    ->name('api.data.receive.raf.csvfile');

// Rota para buscar CSV do banco de dados por ID do relatório
// Requer autenticação via sessão (web middleware)
Route::get('/data/csv/{id}', [DataReceiverController::class, 'getCsv'])
    ->middleware(['web', 'auth'])
    ->name('api.data.csv.get');

// Rota para confirmar uso de créditos e enviar approved/denied para resume_url
// Requer autenticação via sessão (web middleware)
Route::post('/raf/confirm', [DataReceiverController::class, 'confirmCredits'])
    ->middleware(['web', 'auth'])
    ->name('api.raf.confirm');

// Rota para receber notificações de erro do n8n
// Aceita autenticação via token API (para n8n) ou sessão
Route::post('/data/error', [DataReceiverController::class, 'receiveError'])
    ->name('api.data.error');

// Rota para receber progresso de importação de arquivo .txt do Monitoramento
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/monitoramento/sped/importacao-txt/progress', [DataReceiverController::class, 'receiveImportacaoTxtProgress'])
    ->name('api.monitoramento.sped.importacao-txt.progress');

// Rota para receber resultado de consulta do Monitoramento
// n8n envia resultado da consulta (ou pode escrever diretamente no PostgreSQL)
Route::post('/monitoramento/consulta/resultado', [DataReceiverController::class, 'receiveMonitoramentoConsulta'])
    ->name('api.monitoramento.consulta.resultado');

// Rota para receber progresso de importação de XMLs do Monitoramento
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/monitoramento/xml/importacao/progress', [DataReceiverController::class, 'receiveXmlImportacaoProgress'])
    ->name('api.monitoramento.xml.importacao.progress');

// Rota para receber progresso de consulta RAF (lotes) - LEGADO
// n8n envia progresso e resultado final para Laravel armazenar em cache/banco
Route::post('/raf/lote/progress', [DataReceiverController::class, 'receiveRafLoteProgress'])
    ->name('api.raf.lote.progress');

// Rota para receber resultado individual de consulta RAF (por participante) - LEGADO
// n8n envia resultado de cada participante para armazenar em raf_lote_resultados
Route::post('/raf/lote/resultado', [DataReceiverController::class, 'receiveRafLoteResultado'])
    ->name('api.raf.lote.resultado');

// ============================================
// Consultas - Novo padrão (substitui RAF)
// ============================================

// Rota para receber progresso de consulta de lotes
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/consultas/lote/progress', [DataReceiverController::class, 'receiveRafLoteProgress'])
    ->name('api.consultas.lote.progress');

// Rota para receber resultado individual de consulta (por participante)
// n8n envia resultado de cada participante para armazenar em raf_lote_resultados
Route::post('/consultas/lote/resultado', [DataReceiverController::class, 'receiveRafLoteResultado'])
    ->name('api.consultas.lote.resultado');
