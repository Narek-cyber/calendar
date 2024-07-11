<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleServiceController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [GoogleServiceController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleServiceController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleServiceController::class, 'handleGoogleCallback'])->name('auth.google.handle');
Route::get('/logout', [GoogleServiceController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [GoogleServiceController::class, 'dashboard'])->name('dashboard');
    Route::post('/add-event', [GoogleServiceController::class, 'addGoogleCalendarEvent'])->name('add.event');
    Route::get('/edit-event/{id}', [GoogleServiceController::class, 'editGoogleCalendarEvent'])->name('edit.event');
    Route::delete('/event/{id}', [GoogleServiceController::class, 'delete'])->name('event.delete');
});
