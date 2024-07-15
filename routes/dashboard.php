<?php

use App\Http\Controllers\GoogleAuthServiceController;
use App\Http\Controllers\GoogleCalendarServiceController;
use Illuminate\Support\Facades\Route;

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
