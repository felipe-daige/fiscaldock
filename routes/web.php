<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
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
        Route::get('/raf', [DashboardController::class, 'raf'])->name('raf');
        Route::post('/raf/upload', [DashboardController::class, 'uploadSped'])->name('raf.upload');
    });
});

// Rota intermediária sem middleware auth - aceita token API ou sessão
// A autenticação é validada no controller DashboardController
// Esta rota está fora do middleware auth para permitir autenticação via token API
Route::post('/app/solucoes/raf/confirmar', [DashboardController::class, 'confirmarRelatorio'])
    ->name('app.solucoes.raf.confirmar');