<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\CourtBookingController;
use App\Http\Controllers\Web\OwnerLoginController;
use App\Http\Controllers\Web\OwnerRegistrationController;
use App\Http\Controllers\Web\OwnerPasswordSetupController;
use App\Http\Controllers\Web\OwnerBookingCalendarController;
use App\Http\Controllers\Web\OwnerVenueController;
use App\Http\Controllers\Web\UserBookingController;
use App\Http\Controllers\Web\UserReviewController;
use App\Http\Controllers\Web\VenueController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Web\AdminLoginController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AdminVenueController;
use App\Http\Controllers\Web\AdminBookingController;
use App\Http\Controllers\Web\AdminCourtController;
use App\Http\Controllers\Web\FavoriteController;
use App\Http\Controllers\Web\OwnerCancellationPolicyController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\OwnerCourtController;
use App\Http\Controllers\Web\BookingRescheduleController;
use App\Http\Controllers\Web\OwnerBookingRescheduleController;

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
Route::get('/owner/password-setup/{token}', [OwnerPasswordSetupController::class, 'create'])->name('owner.password.setup.create');
Route::post('/owner/password-setup', [OwnerPasswordSetupController::class, 'store'])->name('owner.password.setup.store');
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
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout')->middleware('auth:web');

    // Các route yêu cầu quyền admin
    Route::middleware(['admin'])->group(function () {
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
        Route::post('/venues/{venue}/approve', [AdminVenueController::class, 'approve'])->name('venues.approve');
        Route::post('/venues/{venue}/reject', [AdminVenueController::class, 'reject'])->name('venues.reject');
        Route::get('/venues/{venue}/documents', [AdminVenueController::class, 'documents'])->name('venues.documents');
        
        // Quản lý Lịch đặt
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        
        // Quản lý Sân con (Courts)
        Route::get('/courts', [AdminCourtController::class, 'index'])->name('courts.index');
        Route::get('/courts/{court}', [AdminCourtController::class, 'show'])->name('courts.show');
        Route::patch('/courts/{court}/toggle-status', [AdminCourtController::class, 'toggleStatus'])->name('courts.toggle-status');
        Route::put('/courts/{court}', [AdminCourtController::class, 'update'])->name('courts.update');
        Route::delete('/courts/{court}', [AdminCourtController::class, 'destroy'])->name('courts.destroy');
        Route::post('/courts/batch-update-status', [AdminCourtController::class, 'batchUpdateStatus'])->name('courts.batch-update-status');
        
        Route::get('/reports', [\App\Http\Controllers\Web\AdminReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}/status', [\App\Http\Controllers\Web\AdminReportController::class, 'updateStatus'])->name('reports.update-status');
    });
});

// --- KHU VỰC QUẢN LÝ CỦA CHỦ SÂN (OWNER) ---
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.web.')->group(function () {
    Route::get('/reschedule-requests', [OwnerBookingRescheduleController::class, 'index'])->name('reschedule.index');
    Route::get('/reschedule-requests/{rescheduleRequest}', [OwnerBookingRescheduleController::class, 'show'])->name('reschedule.show');
    Route::post('/reschedule-requests/{rescheduleRequest}/approve', [OwnerBookingRescheduleController::class, 'approve'])->name('reschedule.approve');
    Route::post('/reschedule-requests/{rescheduleRequest}/reject', [OwnerBookingRescheduleController::class, 'reject'])->name('reschedule.reject');
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
    // Quản lý Đánh giá (Bên trong block của Owner)
    Route::get('/reviews', [\App\Http\Controllers\Web\OwnerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Web\OwnerReviewController::class, 'reply'])->name('reviews.reply');
    Route::post('/courts/{court}/lock', [\App\Http\Controllers\Web\OwnerCourtController::class, 'lockSlot']);
    Route::delete('/courts/locks/{lock}', [\App\Http\Controllers\Web\OwnerCourtController::class, 'unlockSlot']);
    // API quản lý Chính sách hủy sân
    Route::get('/venues/{venue}/cancellation-policies', [OwnerCancellationPolicyController::class, 'index']);
    Route::post('/venues/{venue}/cancellation-policies', [OwnerCancellationPolicyController::class, 'store']);
    Route::delete('/venues/{venue}/cancellation-policies/{policy}', [OwnerCancellationPolicyController::class, 'destroy']);
    
    Route::patch('/venues/{venue}/rules', [OwnerVenueController::class, 'updateRules'])->name('venues.update_rules');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookings/{booking}/reschedule', [BookingRescheduleController::class, 'create'])->name('customer.booking.reschedule.create');
    Route::post('/bookings/{booking}/reschedule', [BookingRescheduleController::class, 'store'])->name('customer.booking.reschedule.store');
    
    // Gửi báo cáo sân
    Route::post('/courts/{court}/report', [\App\Http\Controllers\Web\CourtReportController::class, 'store'])->name('web.courts.report');
    
    Route::get('/bookings/{booking}/success', [UserBookingController::class, 'success'])
        ->name('web.bookings.success');
    
    // API thả tim (Đưa ra ngoài account)
    Route::post('/venues/{venue}/favorite', [FavoriteController::class, 'toggle'])->name('web.venues.favorite');
    
    // GOM CHUNG TẤT CẢ CÁC ROUTE CỦA ACCOUNT VÀO MỘT GROUP DUY NHẤT
    Route::prefix('account')->name('account.')->group(function () {
        
        // 1. Lịch sử đặt sân
        Route::get('/bookings', [UserBookingController::class, 'history'])->name('bookings.index');
        Route::get('/reviews', [UserReviewController::class, 'index'])->name('reviews.index');

        // 2. Hủy đặt sân & tính phí
        Route::get('/bookings/{booking}/cancel-fee', [UserBookingController::class, 'calculateCancelFee'])->name('bookings.cancel-fee');
        Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
        
        // 3. Danh sách sân yêu thích
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        
    }); // <-- Ngoặc đóng của group account

}); // <-- NGOẶC ĐÓNG CỦA GROUP AUTH BỊ THIẾU CỦA BẠN CHÍNH LÀ ĐÂY!
