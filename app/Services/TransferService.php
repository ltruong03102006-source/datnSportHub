<?php

namespace App\Services;

use App\Models\VenueTransferRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    /**
     * Xử lý phê duyệt chuyển nhượng với Transaction
     */
    public function approve(VenueTransferRequest $transfer)
    {
        // DB::transaction đảm bảo nếu lỗi ở bất kỳ dòng nào, toàn bộ dữ liệu sẽ được rollback lại như cũ
        DB::transaction(function () use ($transfer) {
            $venue = $transfer->venue;

            // 1. BẢO MẬT: Xóa hồ sơ pháp lý (CCCD, Bank, Giấy phép) của Chủ cũ
            if ($venue->legalDocument) {
                // Tùy chọn nâng cao: Xóa file vật lý trong Storage nếu cần
                $venue->legalDocument()->delete();
            }

            // 2. ĐỔI CHỦ: Cập nhật owner_id sang Chủ mới
            $venue->update([
                'owner_id' => $transfer->to_owner_id
            ]);

            // 3. HOÀN TẤT: Cập nhật trạng thái yêu cầu
            $transfer->update([
                'status' => 'approved'
            ]);
        });
    }

    /**
     * Xử lý từ chối chuyển nhượng
     */
    public function reject(VenueTransferRequest $transfer, string $adminNote)
    {
        $transfer->update([
            'status' => 'rejected',
            'admin_note' => $adminNote
        ]);
    }
}