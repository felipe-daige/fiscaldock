<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

// Rota de API para receber dados via HTTP - aceita token API ou sessão
// No Laravel 12, rotas em api.php recebem automaticamente o prefixo /api
Route::post('/data/receive', [DataReceiverController::class, 'receive'])
    ->name('api.data.receive');

// Rota para buscar dados armazenados em cache por resume_url
// Requer autenticação do usuário via sessão (stateful) para funcionar com frontend
Route::get('/data/receive/{resume_url}', [DataReceiverController::class, 'getData'])
    ->middleware(['web', 'auth'])
    ->where('resume_url', '.*')
    ->name('api.data.get');

// Rota pública para buscar dados armazenados em cache por resume_url
// Não requer autenticação
Route::get('/data/receive-public/{resume_url}', [DataReceiverController::class, 'getDataPublic'])
    ->where('resume_url', '.*')
    ->name('api.data.get.public');

// Rota para buscar os dados mais recentes do cache do usuário
// MOVIDA PARA routes/web.php dentro do grupo de rotas autenticadas
// porque precisa de middleware web para funcionar com sessão

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
