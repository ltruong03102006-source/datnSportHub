<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\CourtBookingController;
use App\Http\Controllers\Web\OwnerLoginController;
use App\Http\Controllers\Web\OwnerRegistrationController;
use App\Http\Controllers\Web\OwnerVenueController;
use App\Http\Controllers\Web\UserBookingController;
use App\Http\Controllers\Web\VenueController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\OwnerCourtController;

Route::get('/', [CourtPageController::class, 'index'])->name('home');

Route::get('/courts/{court}/booking', [CourtBookingController::class, 'show'])->name('web.courts.booking');
Route::post('/courts/booking', [CourtBookingController::class, 'store'])->name('web.courts.booking.store');

Route::get('/venues/{id}', [VenueController::class, 'show'])
    ->whereNumber('id')
    ->name('venues.show');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::get('/owner/login', [OwnerLoginController::class, 'create'])->name('owner.login.page');
Route::post('/owner/login', [OwnerLoginController::class, 'store'])->name('owner.login.store');
Route::get('/owner/register', [OwnerRegistrationController::class, 'create'])->name('owner.register.page');
Route::post('/owner/register', [OwnerRegistrationController::class, 'store'])->name('owner.register.store');
Route::post('/login', [ApiAuthController::class, 'login'])->name('web.login');
Route::post('/register', [ApiAuthController::class, 'register'])->name('web.register');
Route::post('/logout', [ApiAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('web.logout');

// --- KHU VỰC QUẢN LÝ CỦA CHỦ SÂN (OWNER) ---
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.web.')->group(function () {
    Route::get('/venues', [OwnerVenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/create', [OwnerVenueController::class, 'create'])->name('venues.create');
    Route::post('/venues/create', [OwnerVenueController::class, 'store'])->name('venues.store');
    
    // Bổ sung 4 Route mới cho thao tác: Xem chi tiết (Sân con), Sửa, Cập nhật, Xóa
    Route::get('/venues/{venue}', [OwnerVenueController::class, 'show'])->name('venues.show');
    Route::get('/venues/{venue}/edit', [OwnerVenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [OwnerVenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [OwnerVenueController::class, 'destroy'])->name('venues.destroy');
    Route::patch('/venues/{venue}/restore', [OwnerVenueController::class, 'restore'])->name('venues.restore');

    // Thêm route cho quản lý sân con
    Route::post('/venues/{venue}/courts', [OwnerCourtController::class, 'store'])->name('courts.store');
    Route::post('/courts/{court}/generate-slots', [OwnerCourtController::class, 'generateSlots'])->name('courts.generate_slots');
    Route::post('/courts/{court}/slots', [OwnerCourtController::class, 'storeSlot'])->name('courts.store_slot');
    // Thêm route cập nhật thông tin sân con
Route::put('/courts/{court}', [OwnerCourtController::class, 'update'])->name('courts.update');
Route::delete('/courts/{court}', [OwnerCourtController::class, 'destroy'])->name('courts.destroy');
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