<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPage;
use App\Http\Controllers\PosloginController;
use App\Http\Controllers\XmlImportController;
use Illuminate\Support\Facades\Route;



Route::get('/', [LandingPage::class, 'inicio'])->name('home');

Route::get('/inicio', [LandingPage::class, 'inicio'])->name('inicio');
Route::get('/solucoes', [LandingPage::class, 'solucoes'])->name('solucoes');
Route::get('/sobre', [LandingPage::class, 'sobre'])->name('sobre');
Route::get('/beneficios', [LandingPage::class, 'beneficios'])->name('beneficios');
Route::get('/impactos', [LandingPage::class, 'impactos'])->name('impactos');
Route::get('/precos', [LandingPage::class, 'precos'])->name('precos');
Route::get('/faq', [LandingPage::class, 'faq'])->name('faq');
Route::get('/questionario', [LandingPage::class, 'questionario'])->name('questionario');
Route::get('/solucoes/importacao-xml', [LandingPage::class, 'importacaoXml'])->name('solucoes.importacao-xml');
Route::get('/solucoes/conciliacao-bancaria', [LandingPage::class, 'conciliacaoBancaria'])->name('solucoes.conciliacao-bancaria');
Route::get('/solucoes/gestao-cnds', [LandingPage::class, 'gestaoCnds'])->name('solucoes.gestao-cnds');
Route::get('/solucoes/inteligencia-tributaria', [LandingPage::class, 'inteligenciaTributaria'])->name('solucoes.inteligencia-tributaria');
Route::get('/solucoes/raf', [LandingPage::class, 'raf'])->name('solucoes.raf');

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
    Route::get('/dashboard', [PosloginController::class, 'dashboard'])->name('dashboard');
    Route::get('/solucoes', [PosloginController::class, 'solucoes'])->name('autenticado.solucoes');
    Route::get('/solucoes/importacao-xml', [PosloginController::class, 'importacaoXml'])->name('autenticado.solucoes.importacao-xml');
    Route::get('/solucoes/conciliacao-bancaria', [PosloginController::class, 'conciliacaoBancaria'])->name('autenticado.solucoes.conciliacao-bancaria');
    Route::get('/solucoes/gestao-cnds', [PosloginController::class, 'gestaoCnds'])->name('autenticado.solucoes.gestao-cnds');
    Route::get('/solucoes/inteligencia-tributaria', [PosloginController::class, 'inteligenciaTributaria'])->name('autenticado.solucoes.inteligencia-tributaria');
    Route::get('/solucoes/raf', [PosloginController::class, 'raf'])->name('autenticado.solucoes.raf');
});