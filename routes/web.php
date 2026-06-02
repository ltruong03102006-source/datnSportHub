<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourtPageController::class, 'index'])->name('home');

Route::get('/venues/{id}', [VenueController::class, 'show'])
    ->whereNumber('id')
    ->name('venues.show');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
