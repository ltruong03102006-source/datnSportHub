<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\CourtBookingController;
use App\Http\Controllers\Web\OwnerRegistrationController;
use App\Http\Controllers\Web\OwnerVenueController;
use App\Http\Controllers\Web\UserBookingController;
use App\Http\Controllers\Web\VenueController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourtPageController::class, 'index'])->name('home');

Route::get('/courts/{court}/booking', [CourtBookingController::class, 'show'])->name('web.courts.booking');
Route::post('/courts/booking', [CourtBookingController::class, 'store'])->name('web.courts.booking.store');

Route::get('/venues/{id}', [VenueController::class, 'show'])
    ->whereNumber('id')
    ->name('venues.show');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::get('/owner/register', [OwnerRegistrationController::class, 'create'])->name('owner.register.page');
Route::post('/owner/register', [OwnerRegistrationController::class, 'store'])->name('owner.register.store');
Route::post('/login', [ApiAuthController::class, 'login'])->name('web.login');
Route::post('/register', [ApiAuthController::class, 'register'])->name('web.register');
Route::post('/logout', [ApiAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('web.logout');

Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.web.')->group(function () {
    Route::get('/venues', [OwnerVenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/create', [OwnerVenueController::class, 'create'])->name('venues.create');
    Route::post('/venues/create', [OwnerVenueController::class, 'store'])->name('venues.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookings/{booking}/success', [UserBookingController::class, 'success'])
        ->name('web.bookings.success');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/bookings', [UserBookingController::class, 'history'])
            ->name('bookings.index');

        Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])
            ->name('bookings.cancel');
    });
});
