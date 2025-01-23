<?php

use CodebarAg\LaravelInstagram\Http\Controllers\InstagramController;
use Illuminate\Support\Facades\Route;

Route::prefix('instagram')->name('instagram.')->group(function () {
    Route::get('/auth', [InstagramController::class, 'auth'])->name('auth');

    Route::get('/callback', [InstagramController::class, 'callback'])->name('callback');

    Route::get('/me', [InstagramController::class, 'me'])->name('me');

    Route::get('/media', [InstagramController::class, 'media'])->name('media');
});
