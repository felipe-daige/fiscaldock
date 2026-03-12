<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

// ============================================
// Health Check (sem autenticação)
// ============================================
Route::get('/health', [DataReceiverController::class, 'health'])
    ->name('api.health');

// ============================================
// Importação EFD
// ============================================

// Recebe progresso de importação de arquivo EFD (SPED)
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/importacao/efd/importacao-txt/progress', [DataReceiverController::class, 'receiveImportacaoTxtProgress'])
    ->name('api.importacao.efd.importacao-txt.progress');

// ============================================
// Importação XML
// ============================================

// Recebe progresso de importação de XMLs (NF-e, NFS-e, CT-e)
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/importacao/xml/progress', [DataReceiverController::class, 'receiveXmlImportacaoProgress'])
    ->name('api.importacao.xml.progress');

// ============================================
// Monitoramento
// ============================================

// Recebe resultado de consulta do Monitoramento
// n8n envia resultado da consulta (ou pode escrever diretamente no PostgreSQL)
Route::post('/monitoramento/consulta/resultado', [DataReceiverController::class, 'receiveMonitoramentoConsulta'])
    ->name('api.monitoramento.consulta.resultado');

// ============================================
// Consultas de lotes
// ============================================

// Recebe progresso de consulta em lote
// n8n envia progresso para Laravel armazenar em cache (SSE lê do cache)
Route::post('/consulta/progress', [DataReceiverController::class, 'receiveConsultaProgress'])
    ->name('api.consulta.progress');

// Recebe resultado individual de consulta (por participante)
// n8n envia resultado de cada participante para armazenar em consulta_resultados
Route::post('/consultas/lote/resultado', [DataReceiverController::class, 'receiveConsultaLoteResultado'])
    ->name('api.consultas.lote.resultado');

