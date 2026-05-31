<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CourtController;

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
});

// Public court listing & detail (API)
Route::get('/courts/sport/{sportId}', [CourtController::class, 'indexBySport'])
    ->whereNumber('sportId')
    ->name('courts.index_by_sport');

Route::get('/courts/{courtId}', [CourtController::class, 'show'])
    ->whereNumber('courtId')
    ->name('courts.show');