<?php

use App\Http\Controllers\GoogleAuthServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleCalendarServiceController;
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

Route::get('/login', [GoogleAuthServiceController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleAuthServiceController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthServiceController::class, 'handleGoogleCallback'])
    ->name('auth.google.handle');
Route::get('/logout', [GoogleAuthServiceController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [GoogleAuthServiceController::class, 'dashboard'])->name('dashboard');
    Route::post('/add-event', [GoogleCalendarServiceController::class, 'addGoogleCalendarEvent'])
        ->name('event.store');
    Route::get('/edit-event/{id}', [GoogleCalendarServiceController::class, 'editGoogleCalendarEvent'])
        ->name('event.edit');
    Route::put('/update-event/{id}', [GoogleCalendarServiceController::class, 'updateGoogleCalendarEvent'])
        ->name('event.update');
    Route::delete('/event/{id}', [GoogleCalendarServiceController::class, 'delete'])->name('event.delete');
});
