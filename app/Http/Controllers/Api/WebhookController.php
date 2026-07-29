<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PlatformWalletService;
use App\Models\PlatformWalletTransaction;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleSettlement(Request $request, PlatformWalletService $walletService)
    {
        $refId = $request->input('reference_id');
        $status = $request->input('status');
        $message = $request->input('message');

        // Tìm lại giao dịch lúc nãy ta đã Hold tiền
        $transaction = PlatformWalletTransaction::where('reference', $refId)->first();
        if (!$transaction) {
            return response()->json(['error' => 'Giao dịch không tồn tại'], 404);
        }

        if ($status === 'success') {
            // Cập nhật trạng thái thành công
            $transaction->update([
                'description' => 'Rút doanh thu thành công. Mã NH: ' . $request->input('bank_transaction_code')
            ]);
            
            // [TUỲ CHỌN MỞ RỘNG]: Ở đây bạn có thể bắn Job gửi thư "Ting ting - Số dư biến động" tới Email Admin.
            Log::info("Hoàn tất chuyển tiền đối soát: " . $refId);

        } else {
            // NẾU NGÂN HÀNG BÁO LỖI: Cập nhật ghi chú và Hoàn tiền (Rollback) lại vào Két sắt Admin
            $transaction->update([
                'description' => 'Lệnh rút doanh thu thất bại. Lỗi: ' . $message
            ]);

            // Dùng hàm credit() từ Service của bạn để cộng lại tiền[cite: 4]
            $walletService->credit(
                amount: abs($transaction->amount),
                type: 'admin_revenue_refund',
                description: 'Hoàn trả Ví nền tảng do lệnh rút thất bại (Ref: ' . $refId . ')',
                reference: 'REFUND-' . $refId
            );
            
            Log::error("Hoàn tiền đối soát do lỗi ngân hàng: " . $refId);
        }

        return response()->json(['message' => 'Đã tiếp nhận Webhook']);
    }
}