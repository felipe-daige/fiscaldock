<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * Endpoints públicos de API. Restou apenas o health check — todos os webhooks de entrada
 * do n8n (progresso/finalização/divergência de EFD) foram removidos: a extração EFD roda
 * 100% no Laravel (app/Services/Efd + ProcessarEfdImportacaoJob), sem callbacks externos.
 */
class DataReceiverController extends Controller
{
    /**
     * Health check público (liveness). Não expõe token, versão nem ambiente.
     *
     * GET /api/health
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
