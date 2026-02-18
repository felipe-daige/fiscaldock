<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

// ============================================
// Monitoramento - SPED e XML
// ============================================

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

// ============================================
// Consultas de lotes
// ============================================

// Rota para receber progresso de consulta de lotes
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/consultas/lote/progress', [DataReceiverController::class, 'receiveConsultaLoteProgress'])
    ->name('api.consultas.lote.progress');

// Rota para receber resultado individual de consulta (por participante)
// n8n envia resultado de cada participante para armazenar em consulta_resultados
Route::post('/consultas/lote/resultado', [DataReceiverController::class, 'receiveConsultaLoteResultado'])
    ->name('api.consultas.lote.resultado');

// Rota para receber alertas de consultas automáticas
// n8n envia alertas críticos para notificação do usuário
Route::post('/consultas/alertas', [DataReceiverController::class, 'receiveConsultaAlertas'])
    ->name('api.consultas.alertas');
