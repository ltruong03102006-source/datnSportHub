<?php
// Temporary include: add web route for voucher eligibility
use Illuminate\Support\Facades\Route;

Route::get('/courts/{courtId}/available-vouchers', [\App\Http\Controllers\Api\VoucherEligibilityController::class, 'getAvailableVouchers'])
    ->name('courts.available_vouchers.web');
