<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPage;
use App\Http\Controllers\PosloginController;
use Illuminate\Support\Facades\Route;



Route::get('/', [LandingPage::class, 'inicio'])->name('home');

Route::get('/inicio', [LandingPage::class, 'inicio'])->name('inicio');
Route::get('/solucoes', [LandingPage::class, 'solucoes'])->name('solucoes');
Route::get('/beneficios', [LandingPage::class, 'beneficios'])->name('beneficios');
Route::get('/impactos', [LandingPage::class, 'impactos'])->name('impactos');
Route::get('/precos', [LandingPage::class, 'precos'])->name('precos');
Route::get('/faq', [LandingPage::class, 'faq'])->name('faq');
Route::get('/questionario', [LandingPage::class, 'questionario'])->name('questionario');


Route::get('/dashboard', [PosloginController::class, 'dashboard'])->name('dashboard');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/agendar', [AuthController::class, 'showAgendar'])->name('agendar');
Route::post('/agendar', [AuthController::class, 'agendar'])->name('agendar.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');