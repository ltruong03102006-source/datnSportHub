<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\CourtBookingController;
use App\Http\Controllers\Web\OwnerLoginController;
use App\Http\Controllers\Web\OwnerRegistrationController;
use App\Http\Controllers\Web\OwnerBookingCalendarController;
use App\Http\Controllers\Web\OwnerVenueController;
use App\Http\Controllers\Web\UserBookingController;
use App\Http\Controllers\Web\VenueController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Web\AdminLoginController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AdminVenueController;
use App\Http\Controllers\Web\AdminBookingController;
use App\Http\Controllers\Web\AdminCourtController;
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

Route::get('/owner', [\App\Http\Controllers\Web\OwnerDashboardController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.dashboard');

// --- KHU VỰC QUẢN TRỊ VIÊN (ADMIN) ---
Route::prefix('admin')->name('admin.')->group(function () {
    // Đăng nhập Admin (Không yêu cầu đăng nhập)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    // Đăng xuất
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout')->middleware('auth');

    // Các route yêu cầu quyền admin
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Quản lý Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        
        // Quản lý Cơ sở sân
        Route::get('/venues', [AdminVenueController::class, 'index'])->name('venues.index');
        Route::get('/venues/{venue}', function() { return redirect()->route('admin.venues.index'); });
        Route::put('/venues/{venue}', [AdminVenueController::class, 'update'])->name('venues.update');
        Route::delete('/venues/{venue}', [AdminVenueController::class, 'destroy'])->name('venues.destroy');
        
        // Quản lý Lịch đặt
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        
        // Quản lý Sân con (Courts)
        Route::get('/courts', [AdminCourtController::class, 'index'])->name('courts.index');
        Route::get('/courts/{court}', [AdminCourtController::class, 'show'])->name('courts.show');
        Route::patch('/courts/{court}/toggle-status', [AdminCourtController::class, 'toggleStatus'])->name('courts.toggle-status');
        Route::put('/courts/{court}', [AdminCourtController::class, 'update'])->name('courts.update');
        Route::delete('/courts/{court}', [AdminCourtController::class, 'destroy'])->name('courts.destroy');
        Route::post('/courts/batch-update-status', [AdminCourtController::class, 'batchUpdateStatus'])->name('courts.batch-update-status');
        
        // Quản lý đăng ký chủ sân
        Route::get('/owner-registrations', [\App\Http\Controllers\Web\AdminOwnerRegistrationController::class, 'index'])->name('owner-registrations.index');
        Route::post('/owner-registrations/{id}/approve', [\App\Http\Controllers\Web\AdminOwnerRegistrationController::class, 'approve'])->name('owner-registrations.approve');
        Route::post('/owner-registrations/{id}/reject', [\App\Http\Controllers\Web\AdminOwnerRegistrationController::class, 'reject'])->name('owner-registrations.reject');
    });
});

// --- KHU VỰC QUẢN LÝ CỦA CHỦ SÂN (OWNER) ---
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.web.')->group(function () {
    Route::get('/calendar', [OwnerBookingCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [OwnerBookingCalendarController::class, 'events'])->name('calendar.events');
    Route::patch('/calendar/bookings/{booking}/status', [OwnerBookingCalendarController::class, 'updateStatus'])
        ->name('calendar.bookings.status');
    Route::patch('/calendar/bookings/{booking}/cancel', [OwnerBookingCalendarController::class, 'cancel'])
        ->name('calendar.bookings.cancel');

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
Route::delete('/venues/images/{id}', [\App\Http\Controllers\Web\OwnerVenueController::class, 'destroyImage'])->name('owner.venues.images.destroy');
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
