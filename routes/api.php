<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OwnerAuthController;
use App\Http\Controllers\Api\OwnerVenueController;
use App\Http\Controllers\Api\OwnerCourtController;
use App\Http\Controllers\Api\OwnerBookingController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\VenueController;
use App\Http\Controllers\Api\CourtAvailabilityController;
use App\Http\Controllers\Api\AdminOwnerRegistrationController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Web\UserBookingController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Yêu cầu Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Test thử API lấy thông tin user đang đăng nhập
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/courts/{courtId}/booking', [BookingController::class, 'store'])->name('courts.booking');

    // Submit a court review (only after the user has booked it)
    Route::post('/courts/{courtId}/reviews', [ReviewController::class, 'store'])
        ->whereNumber('courtId')
        ->name('courts.reviews.store');
});

// Public sports & court listing & detail (API)
Route::get('/sports', [SportController::class, 'index'])->name('sports.index');

Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');

Route::get('/courts/search', [CourtController::class, 'search'])->name('courts.search');

Route::get('/courts/sport/{sportId}', [CourtController::class, 'indexBySport'])
    ->whereNumber('sportId')
    ->name('courts.index_by_sport');

Route::get('/courts/{courtId}', [CourtController::class, 'show'])
    ->whereNumber('courtId')
    ->name('courts.show');

// Court Availability API
Route::get('/courts/{courtId}/availability', [CourtAvailabilityController::class, 'show'])
    ->name('courts.availability');
// Venues API
Route::get('/venues/{id}', [VenueController::class, 'show'])
    ->whereNumber('id')
    ->name('venues.show');

// List reviews for a venue (aggregated across its courts)
Route::get('/venues/{venueId}/reviews', [ReviewController::class, 'venueReviews'])
    ->whereNumber('venueId')
    ->name('venues.reviews');

/*
|--------------------------------------------------------------------------
| Owner Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('owner')->group(function () {
    Route::match(['GET', 'POST'], '/register', function (Request $request) {
        if ($request->isMethod('GET')) {
            return response()->json([
                'message' => 'Use POST /api/owner/register to create an owner account.',
            ], 405);
        }

        return app(OwnerAuthController::class)->register($request);
    })->name('owner.register');

    Route::match(['GET', 'POST'], '/login', function (Request $request) {
        if ($request->isMethod('GET')) {
            return response()->json([
                'message' => 'Use POST /api/owner/login to log in as an owner.',
            ], 405);
        }

        return app(OwnerAuthController::class)->login($request);
    })->name('owner.login');
});

/*
|--------------------------------------------------------------------------
| Owner Protected Routes (Yêu cầu Authentication + Owner Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'owner'])->prefix('owner')->group(function () {
    Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('owner.logout');
    Route::get('/me', [OwnerAuthController::class, 'me'])->name('owner.me');
    Route::post('/change-password', [OwnerAuthController::class, 'changePassword'])->name('owner.changePassword');
    
    // Owner Venues Management
    Route::get('/sports', [OwnerVenueController::class, 'getSports'])->name('owner.sports');
    Route::get('/venues', [OwnerVenueController::class, 'index'])->name('owner.venues.index');
    Route::post('/venues', [OwnerVenueController::class, 'store'])->name('owner.venues.store');
    Route::get('/venues/{id}', [OwnerVenueController::class, 'show'])->name('owner.venues.show');
    Route::put('/venues/{id}', [OwnerVenueController::class, 'update'])->name('owner.venues.update');
    Route::delete('/venues/{id}', [OwnerVenueController::class, 'destroy'])->name('owner.venues.destroy');
    
    // Owner Courts Management
    Route::get('/courts', [OwnerCourtController::class, 'index'])->name('owner.courts.index');
    Route::post('/venues/{venueId}/courts', [OwnerCourtController::class, 'store'])->name('owner.courts.store');
    Route::put('/courts/{courtId}', [OwnerCourtController::class, 'update'])->name('owner.courts.update');
    Route::delete('/courts/{courtId}', [OwnerCourtController::class, 'destroy'])->name('owner.courts.destroy');
    Route::get('/courts/{courtId}/time-slots', [OwnerCourtController::class, 'getTimeSlots'])->name('owner.courts.timeSlots');
    
    // Owner Bookings Management
    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('owner.bookings.index');
    Route::get('/bookings/stats', [OwnerBookingController::class, 'stats'])->name('owner.bookings.stats');
    Route::get('/bookings/{id}', [OwnerBookingController::class, 'show'])->name('owner.bookings.show');
    Route::post('/bookings/{id}/cancel', [OwnerBookingController::class, 'cancel'])->name('owner.bookings.cancel');
    Route::get('/venues/{venueId}/bookings', [OwnerBookingController::class, 'venueBookings'])->name('owner.venues.bookings');
    Route::get('/courts/{courtId}/bookings', [OwnerBookingController::class, 'courtBookings'])->name('owner.courts.bookings');
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/owner-registrations', [AdminOwnerRegistrationController::class, 'index']);
    Route::get('/owner-registrations/{id}', [AdminOwnerRegistrationController::class, 'show']);
    Route::post('/owner-registrations/{id}/approve', [AdminOwnerRegistrationController::class, 'approve']);
    Route::post('/owner-registrations/{id}/reject', [AdminOwnerRegistrationController::class, 'reject']);
});
