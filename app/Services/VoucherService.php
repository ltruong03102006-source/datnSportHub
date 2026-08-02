<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\SportField;
use Exception;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    /**
     * Create a new voucher
     *
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function create(array $data): Voucher
    {
        // Kiểm tra mã unique nếu có truyền vào
        if (!empty($data['code']) && Voucher::where('code', $data['code'])->exists()) {
            throw new Exception("Voucher code already exists.");
        }

        // Đảm bảo sport_field_id hợp lệ
        if (!empty($data['sport_field_id'])) {
            $sportField = SportField::find($data['sport_field_id']);
            if (!$sportField) {
                throw new Exception("Sport field not found.");
            }
            
            // Tự động gán owner_id nếu chưa có, dựa vào sport field
            if (empty($data['owner_id']) && isset($sportField->owner_id)) {
                $data['owner_id'] = $sportField->owner_id;
            }
        }

        // Đảm bảo owner_id hợp lệ (có thể bổ sung check User model nếu cần)
        if (empty($data['owner_id'])) {
            throw new Exception("Owner ID is required.");
        }

        return DB::transaction(function () use ($data) {
            return Voucher::create($data);
        });
    }
}
