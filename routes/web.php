<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Xml\XmlImportController;
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

// Rotas de API para importação de XMLs
Route::prefix('api/xml')->group(function () {
    Route::post('/upload', [XmlImportController::class, 'upload'])->name('api.xml.upload');
    Route::post('/processar', [XmlImportController::class, 'processar'])->name('api.xml.processar');
    Route::post('/aceitar', [XmlImportController::class, 'aceitarLancamento'])->name('api.xml.aceitar');
    Route::post('/ajustar', [XmlImportController::class, 'ajustarLancamento'])->name('api.xml.ajustar');
    Route::get('/regras', [XmlImportController::class, 'listarRegras'])->name('api.xml.regras');
    Route::post('/regras', [XmlImportController::class, 'criarRegra'])->name('api.xml.regras.create');
    Route::get('/documentos', [XmlImportController::class, 'listarDocumentos'])->name('api.xml.documentos');
});


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/agendar', [AuthController::class, 'showAgendar'])->name('agendar');
Route::post('/agendar', [AuthController::class, 'agendar'])->name('agendar.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

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