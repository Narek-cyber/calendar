<?php

use App\Http\Controllers\GoogleAuthServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [GoogleAuthServiceController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleAuthServiceController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthServiceController::class, 'handleGoogleCallback'])
    ->name('auth.google.handle');
Route::get('/logout', [GoogleAuthServiceController::class, 'logout'])->name('logout');
