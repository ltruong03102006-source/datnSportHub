<?php

namespace App\Services;

use App\Models\Venue;
use App\Models\Setting;

class CommissionService
{
    /**
     * Lấy tỷ lệ hoa hồng áp dụng cho cơ sở (Venue).
     * Ưu tiên lấy cấu hình riêng của Venue. Nếu Null, lấy cấu hình mặc định của hệ thống.
     */
    public function getApplicableRate(Venue $venue): float
    {
        if (!is_null($venue->commission_rate)) {
            return (float) $venue->commission_rate;
        }

        $defaultRate = Setting::where('key', 'default_commission_rate')->value('value');
        
        return (float) ($defaultRate ?? 10.0);
    }

    /**
     * Tính toán số tiền phí nền tảng (Platform Fee) Admin thu được.
     * Làm tròn số nguyên vì tiền VNĐ không có số thập phân lẻ.
     */
    public function calculatePlatformFee(float $totalPrice, float $commissionRate): float
    {
        return round(($totalPrice * $commissionRate) / 100, 0);
    }

    /**
     * Tính toán số tiền chủ sân thực nhận (Owner Earnings).
     * Dùng max(0) để đảm bảo không bao giờ bị âm tiền oan do sai số.
     */
    public function calculateOwnerEarnings(float $totalPrice, float $platformFee): float
    {
        return max(0, $totalPrice - $platformFee);
    }
    
    /**
     * Kiểm tra xem công nợ (balance) của chủ sân có vượt hạn mức (credit limit) không.
     */
    public function isCreditLimitExceeded(float $currentBalance): bool
    {
        $creditLimit = Setting::where('key', 'owner_credit_limit')->value('value');
        $limit = (float) ($creditLimit ?? -1000000);
        
        // Vì credit_limit lưu số âm (ví dụ: -1.000.000), nếu balance nhỏ hơn mức này là vi phạm
        return $currentBalance < $limit;
    }
}