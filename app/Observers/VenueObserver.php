<?php

namespace App\Observers;

use App\Models\Venue;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VenueObserver
{
    /**
     * Handle the Venue "updated" event.
     */
    public function updated(Venue $venue): void
    {
        // Khi sân bị chuyển sang trạng thái ngưng hoạt động ('inactive')
        if ($venue->isDirty('status') && $venue->status === 'inactive') {
            
            // 1. Vô hiệu hóa voucher áp dụng riêng cho sân này (qua sport_field_id)
            Voucher::where('sport_field_id', $venue->id)
                ->where('status', 'active')
                ->update(['status' => 'disabled']);
                
            // 2. Vô hiệu hóa voucher áp dụng chung cho nhiều sân (qua pivot table venue_voucher)
            $voucherIds = DB::table('venue_voucher')
                ->where('venue_id', $venue->id)
                ->pluck('voucher_id');
                
            if ($voucherIds->isNotEmpty()) {
                Voucher::whereIn('id', $voucherIds)
                    ->where('status', 'active')
                    ->update(['status' => 'disabled']);
            }
        }
    }
}
