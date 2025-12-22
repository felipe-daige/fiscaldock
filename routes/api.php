<?php

use App\Http\Controllers\Api\RelatorioCompletoController;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

// Rota sem middleware auth - autenticação é feita no controller via token ou sessão
Route::post('/confirmar-relatorio-completo', [RelatorioCompletoController::class, 'confirmarRelatorioCompleto'])
    ->name('api.confirmar-relatorio-completo');

// Rota alternativa para confirmar relatório RAF - aceita token API ou sessão
// A autenticação é validada no controller RelatorioCompletoController
Route::post('/app/solucoes/raf/confirmar', [DashboardController::class, 'confirmarRelatorio'])
    ->name('api.app.solucoes.raf.confirmar');

