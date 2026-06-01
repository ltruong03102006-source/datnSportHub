<?php

use App\Http\Controllers\Web\CourtPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourtPageController::class, 'index'])->name('home');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
