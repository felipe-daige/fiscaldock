<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

// Rota de API para receber dados via HTTP - aceita token API ou sessão
// No Laravel 12, rotas em api.php recebem automaticamente o prefixo /api
Route::post('/data/receive', [DataReceiverController::class, 'receive'])
    ->name('api.data.receive');

// Rota para buscar dados armazenados em cache por resume_url
// Requer autenticação do usuário
Route::get('/data/receive/{resume_url}', [DataReceiverController::class, 'getData'])
    ->middleware('auth')
    ->where('resume_url', '.*')
    ->name('api.data.get');

// Rota pública para buscar dados armazenados em cache por resume_url
// Não requer autenticação
Route::get('/data/receive-public/{resume_url}', [DataReceiverController::class, 'getDataPublic'])
    ->where('resume_url', '.*')
    ->name('api.data.get.public');

// Rota para buscar os dados mais recentes do cache do usuário
// Requer autenticação
Route::get('/data/receive-latest', [DataReceiverController::class, 'getLatestData'])
    ->middleware('auth')
    ->name('api.data.get.latest');
