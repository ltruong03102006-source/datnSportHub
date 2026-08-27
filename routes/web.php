<?php

use App\Http\Controllers\Web\CourtPageController;
use App\Http\Controllers\Web\CourtBookingController;
use App\Http\Controllers\Web\OwnerLoginController;
use App\Http\Controllers\Web\OwnerRegistrationController;
use App\Http\Controllers\Web\OwnerPasswordSetupController;
use App\Http\Controllers\Web\OwnerBookingCalendarController;
use App\Http\Controllers\Web\OwnerVenueController;
use App\Http\Controllers\Web\OwnerVoucherController;
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
use App\Http\Controllers\Web\CustomerBookingRescheduleController;
use App\Http\Controllers\Web\OwnerBookingRescheduleController;
use App\Http\Controllers\Web\VnPayController;
use App\Http\Controllers\Web\OwnerVenuePackageController;
use App\Http\Controllers\Web\OwnerWalletController;
use App\Http\Controllers\Web\OwnerVoucherController as WebOwnerVoucherController;
use App\Http\Controllers\Web\OwnerWalletTopupController;
use App\Http\Controllers\Web\OwnerWithdrawalController;
use App\Http\Controllers\Web\PackageBookingController;
use App\Http\Controllers\Web\ChatbotController;
use App\Http\Controllers\Web\AdminPackageController;
use App\Http\Controllers\Web\AdminDebtController;
use App\Http\Controllers\Web\AdminFinanceDashboardController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\AdminTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Social login (Google / Facebook)
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Web\SocialAuthController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook'])
    ->name('social.redirect');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Web\SocialAuthController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook'])
    ->name('social.callback');

// VNPay Callback Route
Route::get('/vnpay/callback', [VnPayController::class, 'vnpayReturn'])->name('vnpay.callback');
Route::get('/owner/wallet/topup/vnpay/callback', [OwnerWalletTopupController::class, 'callback'])
    ->name('owner.wallet.topup.callback');

Route::get('/', [CourtPageController::class, 'index'])->name('home');
// API lÆ°u lá»‹ch sá»­ tÃ¬m kiáº¿m Session
Route::post('/save-recent-search', [\App\Http\Controllers\Web\CourtPageController::class, 'saveSearch'])->name('search.save');
Route::get('/rankings', [\App\Http\Controllers\Web\RankingController::class, 'index'])->name('rankings');

Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');
Route::post('/chatbot/reset', [ChatbotController::class, 'reset'])->name('chatbot.reset');

Route::get('/courts/{court}/booking', [CourtBookingController::class, 'show'])->name('web.courts.booking');
Route::post('/courts/booking', [CourtBookingController::class, 'store'])->name('web.courts.booking.store');

// Endpoint: return slot prices for a court on a given date (used by booking page "Xem bảng giá")
Route::get('/courts/{court}/shifts/prices', [CourtBookingController::class, 'prices']);

Route::get('/venues/nearby', [VenueController::class, 'nearbyPage'])->name('venues.nearby');

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

// --- KHU Vá»°C QUáº¢N TRá»Š VIÃŠN (ADMIN) ---
Route::prefix('admin')->name('admin.')->group(function () {
    // ÄÄƒng nháº­p Admin (KhÃ´ng yÃªu cáº§u Ä‘Äƒng nháº­p)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    // ÄÄƒng xuáº¥t
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout')->middleware('auth:web');

    // CÃ¡c route yÃªu cáº§u quyá»n admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Quáº£n lÃ½ Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

        // Quáº£n lÃ½ CÆ¡ sá»Ÿ sÃ¢n
        Route::get('/venues', [AdminVenueController::class, 'index'])->name('venues.index');
        Route::get('/venues/{venue}', function () {
            return redirect()->route('admin.venues.index');
        });
        Route::put('/venues/{venue}', [AdminVenueController::class, 'update'])->name('venues.update');
        Route::delete('/venues/{venue}', [AdminVenueController::class, 'destroy'])->name('venues.destroy');
        Route::post('/venues/{venue}/approve', [AdminVenueController::class, 'approve'])->name('venues.approve');
        Route::post('/venues/{venue}/reject', [AdminVenueController::class, 'reject'])->name('venues.reject');
        Route::get('/venues/{venue}/documents', [AdminVenueController::class, 'documents'])->name('venues.documents');

        // Quáº£n lÃ½ Lá»‹ch Ä‘áº·t
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/refund', [AdminBookingController::class, 'refund'])->name('bookings.refund');

        // Quáº£n lÃ½ SÃ¢n con (Courts)
        Route::get('/courts', [AdminCourtController::class, 'index'])->name('courts.index');
        Route::get('/courts/{court}', [AdminCourtController::class, 'show'])->name('courts.show');
        Route::patch('/courts/{court}/toggle-status', [AdminCourtController::class, 'toggleStatus'])->name('courts.toggle-status');
        Route::put('/courts/{court}', [AdminCourtController::class, 'update'])->name('courts.update');
        Route::delete('/courts/{court}', [AdminCourtController::class, 'destroy'])->name('courts.destroy');
        Route::post('/courts/batch-update-status', [AdminCourtController::class, 'batchUpdateStatus'])->name('courts.batch-update-status');

        Route::get('/reports', [\App\Http\Controllers\Web\AdminReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}/status', [\App\Http\Controllers\Web\AdminReportController::class, 'updateStatus'])->name('reports.update-status');

        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
        Route::get('/finance', [AdminFinanceDashboardController::class, 'index'])->name('finance.index');
        Route::post('/finance/withdraw', [AdminFinanceDashboardController::class, 'withdrawRevenue'])->name('finance.withdraw');
        Route::get('/finance/withdraw-history', [AdminFinanceDashboardController::class, 'withdrawHistory'])->name('finance.withdraw_history');
        Route::get('/debts', [AdminDebtController::class, 'index'])->name('debts.index');
        Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
        Route::get('/chatbot', [\App\Http\Controllers\Web\AdminChatbotController::class, 'index'])->name('chatbot.index');

        // Quáº£n lÃ½ YÃªu cáº§u rÃºt tiá»n
        Route::get('/withdrawals', [\App\Http\Controllers\Web\AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/{withdrawal}', [\App\Http\Controllers\Web\AdminWithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Web\AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Web\AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

        // Cáº¥u hÃ¬nh há»‡ thá»‘ng
        Route::get('/settings', [\App\Http\Controllers\Web\AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Web\AdminSettingController::class, 'store'])->name('settings.store');

        // Quản lý Yêu cầu chuyển nhượng cơ sở
        Route::get('/venue-transfers', [\App\Http\Controllers\Web\AdminVenueTransferController::class, 'index'])->name('venue-transfers.index');
        Route::get('/venue-transfers/{transfer}', [\App\Http\Controllers\Web\AdminVenueTransferController::class, 'show'])->name('venue-transfers.show');
        Route::get('/venue-transfers/{transfer}/contract', [\App\Http\Controllers\Web\AdminVenueTransferController::class, 'contract'])->name('venue-transfers.contract');
        Route::post('/venue-transfers/{transfer}/approve', [\App\Http\Controllers\Web\AdminVenueTransferController::class, 'approve'])->name('venue-transfers.approve');
        Route::post('/venue-transfers/{transfer}/reject', [\App\Http\Controllers\Web\AdminVenueTransferController::class, 'reject'])->name('venue-transfers.reject');
        // 👇 THÊM 2 DÒNG NÀY VÀO ĐỂ XỬ LÝ YÊU CẦU THAY ĐỔI THÔNG TIN (BẢN NHÁP) 👇
        Route::post('/venues/update-requests/{updateRequest}/approve', [AdminVenueController::class, 'approveUpdateReq'])->name('venues.update-requests.approve');
        Route::post('/venues/update-requests/{updateRequest}/reject', [AdminVenueController::class, 'rejectUpdateReq'])->name('venues.update-requests.reject');
        // Cập nhật tỷ lệ hoa hồng cho cơ sở (Admin)
        Route::put('/venues/{venue}/commission', [\App\Http\Controllers\Admin\VenueCommissionController::class, 'update'])->name('venues.commission.update');
        Route::get('/contracts', [\App\Http\Controllers\Web\AdminContractController::class, 'index'])->name('contracts.index');
        Route::get('/contracts/create', [\App\Http\Controllers\Web\AdminContractController::class, 'create'])->name('contracts.create');
        Route::post('/contracts', [\App\Http\Controllers\Web\AdminContractController::class, 'store'])->name('contracts.store');
        Route::get('/contracts/{contract}/edit', [\App\Http\Controllers\Web\AdminContractController::class, 'edit'])->name('contracts.edit');
        Route::put('/contracts/{contract}', [\App\Http\Controllers\Web\AdminContractController::class, 'update'])->name('contracts.update');
        Route::post('/contracts/{contract}/send', [\App\Http\Controllers\Web\AdminContractController::class, 'send'])->name('contracts.send');
        Route::get('/contracts/{contract}/pdf', [\App\Http\Controllers\Web\AdminContractController::class, 'exportPdf'])->name('contracts.pdf');
        Route::get('/contracts/{contract}', [\App\Http\Controllers\Web\AdminContractController::class, 'show'])->name('contracts.show');
        Route::post('/contracts/{contract}/terminate', [\App\Http\Controllers\Web\AdminContractController::class, 'terminate'])->name('contracts.terminate');

        // ❌ 2 DÒNG NÀY ĐANG NẰM NGOÀI NHÓM ADMIN NÊN BỊ LỖI MẤT CHỮ 'admin.'
Route::get('/financial-settings', [\App\Http\Controllers\Admin\FinancialSettingController::class, 'index'])->name('financial-settings.index');
Route::post('/financial-settings', [\App\Http\Controllers\Admin\FinancialSettingController::class, 'update'])->name('financial-settings.update');
    });
});

//chu san
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.web.')->group(function () {
    // Danh sách sân con của 1 cơ sở, dùng cho bộ lọc thống kê
    Route::get('/venues/{venue}/courts-lookup', [\App\Http\Controllers\Web\OwnerCourtLookupController::class, 'index'])
        ->name('venues.courts_lookup');

    Route::get('/wallet', [OwnerWalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/topup', [OwnerWalletTopupController::class, 'create'])->name('wallet.topup.create');
    Route::post('/wallet/topup', [OwnerWalletTopupController::class, 'store'])->name('wallet.topup.store');
    Route::get('/wallet/bank', [OwnerWalletController::class, 'editBank'])->name('wallet.bank.edit');
    Route::put('/wallet/bank', [OwnerWalletController::class, 'updateBank'])->name('wallet.bank.update');
    Route::get('/withdrawals', [OwnerWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [OwnerWithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [OwnerWithdrawalController::class, 'store'])->name('withdrawals.store');

    Route::get('/reschedule-requests', [OwnerBookingRescheduleController::class, 'index'])->name('reschedule.index');
    Route::get('/reschedule-requests/{requestCode}', [OwnerBookingRescheduleController::class, 'show'])->name('reschedule.show');
    Route::post('/reschedule-requests/{requestCode}/approve', [OwnerBookingRescheduleController::class, 'approve'])->name('reschedule.approve');
    Route::post('/reschedule-requests/{requestCode}/reject', [OwnerBookingRescheduleController::class, 'reject'])->name('reschedule.reject');
    Route::get('/calendar', [OwnerBookingCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [OwnerBookingCalendarController::class, 'events'])->name('calendar.events');
    Route::patch('/calendar/bookings/{booking}/status', [OwnerBookingCalendarController::class, 'updateStatus'])
        ->name('calendar.bookings.status');
    Route::patch('/calendar/bookings/{booking}/cancel', [OwnerBookingCalendarController::class, 'cancel'])
        ->name('calendar.bookings.cancel');

    Route::get('/venues', [OwnerVenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/create', [OwnerVenueController::class, 'create'])->name('venues.create');
    Route::post('/venues/create', [OwnerVenueController::class, 'store'])->name('venues.store');

    // Bá»• sung 4 Route má»›i cho thao tÃ¡c: Xem chi tiáº¿t (SÃ¢n con), Sá»­a, Cáº­p nháº­t, XÃ³a
    Route::get('/venues/{venue}', [OwnerVenueController::class, 'show'])->name('venues.show');
    Route::get('/venues/{venue}/edit', [OwnerVenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [OwnerVenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [OwnerVenueController::class, 'destroy'])->name('venues.destroy');
    Route::patch('/venues/{venue}/restore', [OwnerVenueController::class, 'restore'])->name('venues.restore');

    // ThÃªm route cho quáº£n lÃ½ sÃ¢n con
    Route::post('/venues/{venue}/courts', [OwnerCourtController::class, 'store'])->name('courts.store');
    Route::post('/courts/{court}/generate-slots', [OwnerCourtController::class, 'generateSlots'])->name('courts.generate_slots');
    Route::post('/courts/{court}/slots', [OwnerCourtController::class, 'storeSlot'])->name('courts.store_slot');
    // ThÃªm route cáº­p nháº­t thÃ´ng tin sÃ¢n con
    Route::put('/courts/{court}', [OwnerCourtController::class, 'update'])->name('courts.update');
    Route::delete('/courts/{court}', [OwnerCourtController::class, 'destroy'])->name('courts.destroy');
    Route::delete('/venues/images/{id}', [\App\Http\Controllers\Web\OwnerVenueController::class, 'destroyImage'])->name('owner.venues.images.destroy');
    // Quáº£n lÃ½ ÄÃ¡nh giÃ¡ (BÃªn trong block cá»§a Owner)
    Route::get('/reviews', [\App\Http\Controllers\Web\OwnerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Web\OwnerReviewController::class, 'reply'])->name('reviews.reply');
    Route::post('/courts/{court}/lock', [\App\Http\Controllers\Web\OwnerCourtController::class, 'lockSlot']);
    Route::delete('/courts/locks/{lock}', [\App\Http\Controllers\Web\OwnerCourtController::class, 'unlockSlot']);
    // API quáº£n lÃ½ ChÃ­nh sÃ¡ch há»§y sÃ¢n
    Route::get('/venues/{venue}/cancellation-policies', [OwnerCancellationPolicyController::class, 'index']);
    Route::post('/venues/{venue}/cancellation-policies', [OwnerCancellationPolicyController::class, 'store']);
    Route::delete('/venues/{venue}/cancellation-policies/{policy}', [OwnerCancellationPolicyController::class, 'destroy']);

    Route::patch('/venues/{venue}/rules', [OwnerVenueController::class, 'updateRules'])->name('venues.update_rules');

    Route::get('/packages', [OwnerVenuePackageController::class, 'index'])->name('packages.index');
    Route::post('/venues/{venue}/packages/toggle-booking', [OwnerVenuePackageController::class, 'toggleVenue'])->name('venues.packages.toggle-booking');
    Route::get('/venues/{venue}/packages/create', [OwnerVenuePackageController::class, 'create'])->name('venues.packages.create');
    Route::post('/venues/{venue}/packages', [OwnerVenuePackageController::class, 'store'])->name('venues.packages.store');
    Route::get('/venues/{venue}/packages/{package}/edit', [OwnerVenuePackageController::class, 'edit'])->name('venues.packages.edit');
    Route::put('/venues/{venue}/packages/{package}', [OwnerVenuePackageController::class, 'update'])->name('venues.packages.update');
    Route::delete('/venues/{venue}/packages/{package}', [OwnerVenuePackageController::class, 'destroy'])->name('venues.packages.destroy');
    Route::patch('/venues/{venue}/packages/{package}/toggle', [OwnerVenuePackageController::class, 'togglePackage'])->name('venues.packages.toggle');
    // Quáº£n lÃ½ Dá»‹ch vá»¥ Ä‘i kÃ¨m
    Route::get('/services', [\App\Http\Controllers\Web\OwnerServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [\App\Http\Controllers\Web\OwnerServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [\App\Http\Controllers\Web\OwnerServiceController::class, 'update'])->name('services.update');
    Route::patch('/services/{service}/toggle', [\App\Http\Controllers\Web\OwnerServiceController::class, 'toggleActive'])->name('services.toggle');
    Route::delete('/services/{service}', [\App\Http\Controllers\Web\OwnerServiceController::class, 'destroy'])->name('services.destroy');

    // Quản lý Vouchers
    Route::get('/vouchers', [OwnerVoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [OwnerVoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [OwnerVoucherController::class, 'store'])->name('vouchers.store');

    // API Check Email (Phải đặt trước route có tham số {venue})
    Route::post('/venues/transfer/check-email', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'checkEmail'])->name('venues.transfer.check-email');
    // Chuyển nhượng cơ sở (Hợp đồng chuyển nhượng)
    Route::get('/venues/transfer/create', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'create'])->name('venues.transfer.general_create');
    Route::post('/venues/transfer/store', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'store'])->name('venues.transfer.general_store');
    Route::get('/venues/{venue}/transfer', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'create'])->name('venues.transfer.create');
    Route::post('/venues/{venue}/transfer', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'store'])->name('venues.transfer.store');
    Route::get('/venues/transfers/history', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'history'])->name('venues.transfers.history');
    // Hiển thị chi tiết Hợp đồng chuyển nhượng
    Route::get('/venues/transfers/{transfer}/show', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'show'])
        ->name('venues.transfers.show');

    // Hiển thị form điền pháp lý cho chủ mới
    Route::get('venues/transfers/{transfer}/accept', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'showAcceptForm'])
        ->name('venues.transfers.accept');

    // Gửi thông báo hợp đồng chuyển nhượng đến Bên nhận
    Route::post('venues/transfers/{transfer}/send', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'sendNotification'])
        ->name('venues.transfers.send');

    // Xử lý nộp form pháp lý
    Route::post('venues/transfers/{transfer}/accept', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'submitAcceptForm'])
        ->name('venues.transfers.accept.submit');

    // Xử lý Ký hợp đồng chuyển nhượng (Bên B)
    Route::post('venues/transfers/{transfer}/sign', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'signContract'])
        ->name('venues.transfers.sign');

    // Xử lý Hủy hợp đồng chuyển nhượng
    Route::post('venues/transfers/{transfer}/cancel', [\App\Http\Controllers\Web\OwnerVenueTransferController::class, 'cancelTransfer'])
        ->name('venues.transfers.cancel');

    // Quản lý Mã giảm giá (Vouchers)
    Route::get('/vouchers/report', [WebOwnerVoucherController::class, 'report'])->name('vouchers.report');
    Route::get('/vouchers', [WebOwnerVoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [WebOwnerVoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [WebOwnerVoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{id}', [WebOwnerVoucherController::class, 'show'])->name('vouchers.show');
    Route::get('/vouchers/{id}/edit', [WebOwnerVoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{id}', [WebOwnerVoucherController::class, 'update'])->name('vouchers.update');
    Route::post('/vouchers/{id}/extend', [WebOwnerVoucherController::class, 'extend'])->name('vouchers.extend');
    Route::patch('/vouchers/{id}/toggle-status', [WebOwnerVoucherController::class, 'toggleStatus'])->name('vouchers.toggle-status');
    Route::delete('/vouchers/{id}', [WebOwnerVoucherController::class, 'destroy'])->name('vouchers.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');

    Route::get('/venues/{venue}/package-booking', [PackageBookingController::class, 'create'])
        ->name('package-bookings.create');

    Route::post('/package-bookings', [PackageBookingController::class, 'store'])
        ->name('package-bookings.store');

    Route::get('/package-bookings/{bookingPackage}', [PackageBookingController::class, 'show'])
        ->name('package-bookings.show');

    Route::post('/package-bookings/{bookingPackage}/payment-success', [PackageBookingController::class, 'paymentSuccess'])
        ->name('package-bookings.payment-success');

    Route::get('/package-bookings/{bookingPackage}/payment/vnpay', [PackageBookingController::class, 'showVnpay'])
        ->name('package-bookings.payment.vnpay');

    Route::get('/package-bookings/{bookingPackage}/payment/vnpay/start', [PackageBookingController::class, 'startVnpay'])
        ->name('package-bookings.payment.vnpay_start');

    Route::patch('/package-bookings/{bookingPackage}/pause', [PackageBookingController::class, 'pause'])
        ->name('package-bookings.pause');

    Route::patch('/package-bookings/{bookingPackage}/resume', [PackageBookingController::class, 'resume'])
        ->name('package-bookings.resume');

    Route::match(['POST', 'DELETE'], '/package-bookings/{bookingPackage}/cancel', [PackageBookingController::class, 'cancel'])
        ->name('package-bookings.cancel');

    Route::get('/bookings/{booking}/reschedule', [CustomerBookingRescheduleController::class, 'create'])->name('customer.booking.reschedule.create');
    Route::post('/bookings/{booking}/reschedule', [CustomerBookingRescheduleController::class, 'store'])->name('customer.booking.reschedule.store');

    // Gá»­i bÃ¡o cÃ¡o sÃ¢n
    Route::post('/courts/{court}/report', [\App\Http\Controllers\Web\CourtReportController::class, 'store'])->name('web.courts.report');

    Route::get('/bookings/{booking}/success', [UserBookingController::class, 'success'])
        ->name('web.bookings.success');

    // Thanh toÃ¡n VNPay
    Route::get('/bookings/{booking}/payment/vnpay-qr', [\App\Http\Controllers\Web\VnPayController::class, 'showVnpayQr'])
        ->name('bookings.payment.vnpay_qr');
    Route::get('/bookings/{booking}/payment/vnpay/start', [\App\Http\Controllers\Web\VnPayController::class, 'startVnpay'])
        ->name('bookings.payment.vnpay_start');
    Route::get('/vnpay/payment/{booking}', [\App\Http\Controllers\Web\VnPayController::class, 'createPayment'])
        ->name('vnpay.payment');

    // API tháº£ tim (ÄÆ°a ra ngoÃ i account)
    Route::post('/venues/{venue}/favorite', [FavoriteController::class, 'toggle'])->name('web.venues.favorite');

    // GOM CHUNG Táº¤T Cáº¢ CÃC ROUTE Cá»¦A ACCOUNT VÃ€O Má»˜T GROUP DUY NHáº¤T
    Route::prefix('account')->name('account.')->group(function () {

        // 1. Lá»‹ch sá»­ Ä‘áº·t sÃ¢n
        Route::get('/bookings', [UserBookingController::class, 'history'])->name('bookings.index');
        Route::get('/reviews', [UserReviewController::class, 'index'])->name('reviews.index');

        // 2. Há»§y Ä‘áº·t sÃ¢n & tÃ­nh phÃ­
        Route::get('/bookings/{booking}/cancel-fee', [UserBookingController::class, 'calculateCancelFee'])->name('bookings.cancel-fee');
        Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');

        // 3. Danh sÃ¡ch sÃ¢n yÃªu thÃ­ch
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

        // 4. Trang cÃ¡ nhÃ¢n
        Route::get('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'updateInfo'])->name('profile.update');
        Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [\App\Http\Controllers\Web\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::put('/profile/bank', [\App\Http\Controllers\Web\ProfileController::class, 'updateBankInfo'])->name('profile.bank');

        // 5. RÃºt tiá»n vÃ­
        Route::post('/wallet/withdraw', [\App\Http\Controllers\Web\WalletController::class, 'withdraw'])->name('wallet.withdraw');
    }); // <-- Ngoáº·c Ä‘Ã³ng cá»§a group account

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [\App\Http\Controllers\NotificationController::class, 'latest'])->name('notifications.latest');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    // --- Cá»˜NG Äá»’NG: TÃŒM Äá»I / Báº®T Cáº¶P ---
    // --- Cá»˜NG Äá»’NG: TÃŒM Äá»I / Báº®T Cáº¶P ---
    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\MatchPostController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\Web\MatchPostController::class, 'store'])->name('store');

        Route::get('/my-posts', [\App\Http\Controllers\Web\MatchPostController::class, 'myPosts'])->name('my_posts');
        Route::patch('/{matchPost}/close', [\App\Http\Controllers\Web\MatchPostController::class, 'closePost'])->name('close');
        Route::delete('/{matchPost}', [\App\Http\Controllers\Web\MatchPostController::class, 'destroy'])->name('destroy');

        // CHá»¨C NÄ‚NG TÃŒM Äá»I 2.0 (XIN JOIN, DUYá»†T & Tá»ª CHá»I)
        Route::post('/{matchPost}/join', [\App\Http\Controllers\Web\MatchPostController::class, 'join'])->name('join');
        Route::patch('/participant/{participant}/approve', [\App\Http\Controllers\Web\MatchPostController::class, 'approveParticipant'])->name('approve');

        // THÃŠM ÄÃšNG DÃ’NG NÃ€Y VÃ€O LÃ€ Háº¾T Lá»–I 500 NAY:
        Route::patch('/participant/{participant}/reject', [\App\Http\Controllers\Web\MatchPostController::class, 'rejectParticipant'])->name('reject');
        // RÃºt lui khá»i kÃ¨o (NgÆ°á»i xin tham gia tá»± há»§y)
        Route::delete('/{matchPost}/cancel-join', [\App\Http\Controllers\Web\MatchPostController::class, 'cancelJoin'])->name('cancel_join');
    });
}); // <-- NGOáº¶C ÄÃ“NG Cá»¦A GROUP AUTH Bá»Š THIáº¾U Cá»¦A Báº N CHÃNH LÃ€ ÄÃ‚Y!

Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/contracts', [\App\Http\Controllers\Web\OwnerContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{contract}', [\App\Http\Controllers\Web\OwnerContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/download', [\App\Http\Controllers\Web\OwnerContractController::class, 'download'])->name('contracts.download');
    Route::post('/contracts/{contract}/accept', [\App\Http\Controllers\Web\OwnerContractController::class, 'accept'])->name('contracts.accept');
    Route::post('/contracts/{contract}/reject', [\App\Http\Controllers\Web\OwnerContractController::class, 'reject'])->name('contracts.reject');
});
