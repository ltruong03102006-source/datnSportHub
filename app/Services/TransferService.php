<?php

namespace App\Services;

use App\Models\VenueTransferRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    /**
     * Xử lý phê duyệt chuyển nhượng với Transaction
     */
    public function approve(VenueTransferRequest $transfer)
    {
        // ==========================================
        // KHỐI 1: KIỂM TRA ĐIỀU KIỆN (COMMIT 3)
        // ==========================================
        
        // 1. Kiểm tra trạng thái yêu cầu có đang "Chờ duyệt" không?
        if ($transfer->status !== 'pending') {
            throw new Exception('Yêu cầu này đã được xử lý hoặc không hợp lệ.');
        }

        $venue = $transfer->venue;

        // 2. Kiểm tra xem cơ sở có tồn tại và chủ cũ có bị thay đổi không?
        if (!$venue || $venue->owner_id !== $transfer->from_owner_id) {
            throw new Exception('Cơ sở này đã bị đổi chủ hoặc không tồn tại, không thể duyệt chuyển nhượng.');
        }

        // 3. Kiểm tra tính hợp lệ của Chủ mới
        $newOwner = User::find($transfer->to_owner_id);
        if (!$newOwner || $newOwner->role !== 'owner') {
            throw new Exception('Tài khoản nhận chuyển nhượng không tồn tại hoặc không đủ quyền Chủ sân.');
        }

        // ==========================================
        // KHỐI 2: XỬ LÝ GIAO DỊCH (COMMIT 1 & 2)
        // ==========================================
        DB::transaction(function () use ($transfer, $venue) {
            // 1. Bảo mật: Xóa hồ sơ pháp lý của Chủ cũ
            if ($venue->legalDocument) {
                $venue->legalDocument()->delete();
            }

            // 2. Đổi quyền sở hữu
            $venue->update([
                'owner_id' => $transfer->to_owner_id
            ]);

            // 3. Hoàn tất yêu cầu
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
        if ($transfer->status !== 'pending') {
            throw new Exception('Yêu cầu này đã được xử lý trước đó.');
        }

        $transfer->update([
            'status' => 'rejected',
            'admin_note' => $adminNote
        ]);
    }
}