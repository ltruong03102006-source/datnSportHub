<?php
namespace App\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SimulatedSettlementGateway implements SettlementGatewayInterface
{
    public function processPayout(string $referenceId, float $amount, array $bankInfo): void
    {
        // Đẩy tác vụ vào Job/Queue ngầm để không bắt Admin phải chờ màn hình load
        dispatch(function () use ($referenceId, $amount) {
            sleep(3); // Giả lập thời gian ngân hàng duyệt lệnh mất 3 giây
            
            // Tỷ lệ 90% giao dịch thành công, 10% báo lỗi để test tính năng hoàn tiền (Rollback)
            $isSuccess = rand(1, 100) <= 90; 

            // Ngân hàng xử lý xong, bắn Webhook ngược lại hệ thống Backend của bạn
            Http::post(url('/api/webhooks/settlement'), [
                'reference_id' => $referenceId,
                'status' => $isSuccess ? 'success' : 'failed',
                'bank_transaction_code' => $isSuccess ? 'SIM-BANK-' . Str::upper(Str::random(6)) : null,
                'message' => $isSuccess ? 'Chuyển khoản đối soát thành công' : 'Ngân hàng từ chối giao dịch'
            ]);
        });
    }
}