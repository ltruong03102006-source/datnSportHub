<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $voucher = $this->resource['voucher'];
        $statistics = $this->resource['statistics'];
        $usedBookings = $this->resource['used_bookings'];

        return [
            'general_info' => [
                'id' => $voucher->id,
                'name' => $voucher->name,
                'code' => $voucher->code,
                'discount_type' => $voucher->discount_type,
                'discount_value' => (float) $voucher->discount_value,
                'min_booking_value' => $voucher->min_booking_value ? (float) $voucher->min_booking_value : null,
                'max_discount_amount' => $voucher->max_discount_amount ? (float) $voucher->max_discount_amount : null,
                'applies_to_all_fields' => (bool) $voucher->applies_to_all_fields,
                'time_slots' => $voucher->time_slots,
                'apply_days' => $voucher->apply_days,
                'start_date' => $voucher->start_date ? $voucher->start_date->format('Y-m-d H:i:s') : null,
                'end_date' => $voucher->end_date ? $voucher->end_date->format('Y-m-d H:i:s') : null,
                'usage_limit' => $voucher->usage_limit,
                'used_count' => $voucher->used_count,
                'status' => $voucher->status,
                'venues' => $voucher->venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                    ];
                }),
            ],
            'statistics' => [
                'used_count' => $statistics['used_count'],
                'total_discount' => $statistics['total_discount'],
                'max_booking_revenue' => $statistics['max_booking_revenue'],
                'min_booking_revenue' => $statistics['min_booking_revenue'],
                'usage_rate' => $statistics['usage_rate'],
            ],
            'used_bookings' => $usedBookings,
        ];
    }
}
