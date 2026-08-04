<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Venue;
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
        // Tự động sinh mã nếu không có
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode();
        } else {
            // Kiểm tra mã unique nếu có truyền vào
            if (Voucher::where('code', $data['code'])->exists()) {
                throw new Exception("Voucher code already exists.");
            }
        }

        // Đảm bảo owner_id hợp lệ
        if (empty($data['owner_id'])) {
            throw new Exception("Owner ID is required.");
        }

        // Validate venues ownership
        $venueIds = $data['venue_ids'] ?? [];
        if (!empty($venueIds)) {
            $this->validateOwnership($data['owner_id'], $venueIds);
        }

        return DB::transaction(function () use ($data, $venueIds) {
            $voucher = Voucher::create($data);
            
            if (!empty($venueIds)) {
                $voucher->venues()->sync($venueIds);
            }
            
            return $voucher;
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
    public function validateOwnership(int $ownerId, $venueIds): bool
    {
        $ids = (array) $venueIds;

        $count = Venue::whereIn('id', $ids)
            ->where('owner_id', $ownerId)
            ->count();

        if ($count !== count($ids)) {
            throw new Exception("Owner does not own all the provided venues.");
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

    /**
     * Disable a voucher
     *
     * @param int $voucherId
     * @return Voucher
     */
    public function disable(int $voucherId): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);
        $voucher->status = 'disabled';
        $voucher->save();

        return $voucher;
    }

    /**
     * Extend voucher end date and reactivate if expired
     *
     * @param int $voucherId
     * @param string $newEndDate
     * @return Voucher
     */
    public function extend(int $voucherId, string $newEndDate): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);
        $voucher->end_date = $newEndDate;
        
        if ($voucher->status === 'expired' || $voucher->status === 'disabled') {
            $voucher->status = 'active';
        }
        
        $voucher->save();

        return $voucher;
    }
}
