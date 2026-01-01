<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RafController;
use Illuminate\Support\Facades\Route;



Route::get('/', [LandingPageController::class, 'inicio'])->name('home');

Route::get('/inicio', [LandingPageController::class, 'inicio'])->name('inicio');
Route::get('/solucoes', [LandingPageController::class, 'solucoes'])->name('solucoes');
Route::get('/sobre', [LandingPageController::class, 'sobre'])->name('sobre');
Route::get('/beneficios', [LandingPageController::class, 'beneficios'])->name('beneficios');
Route::get('/impactos', [LandingPageController::class, 'impactos'])->name('impactos');
Route::get('/precos', [LandingPageController::class, 'precos'])->name('precos');
Route::get('/faq', [LandingPageController::class, 'faq'])->name('faq');
Route::get('/questionario', [LandingPageController::class, 'questionario'])->name('questionario');
Route::get('/solucoes/importacao-xml', [LandingPageController::class, 'importacaoXml'])->name('solucoes.importacao-xml');
Route::get('/solucoes/conciliacao-bancaria', [LandingPageController::class, 'conciliacaoBancaria'])->name('solucoes.conciliacao-bancaria');
Route::get('/solucoes/gestao-cnds', [LandingPageController::class, 'gestaoCnds'])->name('solucoes.gestao-cnds');
Route::get('/solucoes/inteligencia-tributaria', [LandingPageController::class, 'inteligenciaTributaria'])->name('solucoes.inteligencia-tributaria');
Route::get('/solucoes/raf', [LandingPageController::class, 'raf'])->name('solucoes.raf');
Route::post('/raf/upload-public', [LandingPageController::class, 'uploadSpedPublic'])->name('raf.upload.public');
Route::post('/raf/cancel-public', [LandingPageController::class, 'cancelSpedPublic'])->name('raf.cancel.public');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/agendar', [AuthController::class, 'showAgendar'])->name('agendar');
Route::post('/agendar', [AuthController::class, 'agendar'])->name('agendar.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/app/perfil', [DashboardController::class, 'perfil'])->name('app.perfil');

    // Rotas de créditos
    Route::prefix('app/credits')->name('app.credits.')->group(function () {
        Route::get('/balance', [CreditController::class, 'balance'])->name('balance');
        Route::post('/confirm', [CreditController::class, 'confirm'])->name('confirm');
        Route::post('/cancel', [CreditController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('app/solucoes')->name('app.solucoes.')->group(function () {
        Route::get('/', [DashboardController::class, 'solucoes'])->name('index');
        Route::get('/importacao-xml', [DashboardController::class, 'importacaoXml'])->name('importacao-xml');
        Route::get('/conciliacao-bancaria', [DashboardController::class, 'conciliacaoBancaria'])->name('conciliacao-bancaria');
        Route::get('/gestao-cnds', [DashboardController::class, 'gestaoCnds'])->name('gestao-cnds');
        Route::get('/inteligencia-tributaria', [DashboardController::class, 'inteligenciaTributaria'])->name('inteligencia-tributaria');
    });

    // Rotas RAF (movidas de app/solucoes para app/raf)
    Route::get('/app/raf', [DashboardController::class, 'raf'])->name('app.raf');
    Route::post('/app/raf/upload', [DashboardController::class, 'uploadSped'])->name('app.raf.upload');
    
    // Rotas de histórico de relatórios RAF
    Route::get('/app/raf/historico', [RafController::class, 'historico'])->name('app.raf.historico');
    Route::get('/app/raf/detalhes/{id}', [RafController::class, 'detalhes'])->name('app.raf.detalhes');
    Route::post('/app/raf/confirmar/{id}', [RafController::class, 'confirmar'])->name('app.raf.confirmar');
    Route::post('/app/raf/cancelar/{id}', [RafController::class, 'cancelar'])->name('app.raf.cancelar');

    // Rota de Importação de SPED
    Route::get('/app/sped_importar', [DashboardController::class, 'spedImportar'])->name('app.sped.importar');

    // Rota de Análise de Risco SPED
    Route::get('/app/sped-analise-risco', [DashboardController::class, 'spedAnaliseRisco'])->name('app.sped.analise.risco');

    // Rota de validação de XML
    Route::get('/app/validar_xml', [DashboardController::class, 'validarXml'])->name('app.validar_xml');
    
    // Rota de Análise de Risco XMLs
    Route::get('/app/xml_analise_risco', [DashboardController::class, 'xmlAnaliseRisco'])->name('app.xml.analise.risco');
    
    // Rota de Novo Cliente
    Route::get('/app/novo_cliente', [DashboardController::class, 'novoCliente'])->name('app.novo.cliente');
    
    // Rota de Clientes
    Route::get('/app/clientes', [DashboardController::class, 'clientes'])->name('app.clientes');
    
    // Rota de Consultar Inscrição Estadual
    Route::get('/app/consultar', [DashboardController::class, 'consultarInscricaoEstadual'])->name('app.consultar');
    
    // Rota de Consultar Listas Restritivas
    Route::get('/app/consultar_listas_restritivas', [DashboardController::class, 'consultarListasRestritivas'])->name('app.consultar.listas.restritivas');
    
    // Rota SSE para notificações em tempo real
    Route::get('/api/data/notifications/stream', [\App\Http\Controllers\Api\DataReceiverController::class, 'streamNotifications'])
        ->name('api.data.notifications.stream');
});