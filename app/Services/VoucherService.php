<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\SportField;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * Generate a unique voucher code
     *
     * @param string $prefix
     * @return string
     */
    public function generateCode(string $prefix = 'FIELD'): string
    {
        do {
            $code = strtoupper($prefix . '-' . Str::random(6));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Validate if the owner actually owns the given sport fields
     *
     * @param int $ownerId
     * @param array|int $sportFieldIds
     * @return bool
     * @throws Exception
     */
    public function validateOwnership(int $ownerId, $sportFieldIds): bool
    {
        $ids = (array) $sportFieldIds;

        $count = SportField::whereIn('id', $ids)
            ->where('owner_id', $ownerId)
            ->count();

        if ($count !== count($ids)) {
            throw new Exception("Owner does not own all the provided sport fields.");
        }

        return true;
    }

    /**
     * Update an existing voucher
     *
     * @param int $voucherId
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function update(int $voucherId, array $data): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $voucherId)->exists()) {
            throw new Exception("Cannot update voucher. It has already been used.");
        }

        // Validate code uniqueness if it's being updated
        if (isset($data['code']) && $data['code'] !== $voucher->code && Voucher::where('code', $data['code'])->exists()) {
            throw new Exception("Voucher code already exists.");
        }

        return DB::transaction(function () use ($voucher, $data) {
            $voucher->update($data);
            return $voucher;
        });
    }

    /**
     * Delete a voucher (soft delete)
     *
     * @param int $voucherId
     * @return bool
     * @throws Exception
     */
    public function delete(int $voucherId): bool
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $voucherId)->exists()) {
            throw new Exception("Cannot delete voucher. It has already been used.");
        }

        return DB::transaction(function () use ($voucher) {
            return $voucher->delete();
        });
    }
}
