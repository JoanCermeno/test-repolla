<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('tablero', 'pages.tablero')->name('tablero');

Route::view('loteria', 'pages.loteria')
    ->middleware(['auth'])
    ->name('loteria');

Route::view('mis-reservas', 'pages.mis-reservas')
    ->middleware(['auth'])
    ->name('mis-reservas');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
