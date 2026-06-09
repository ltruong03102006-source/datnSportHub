<?php

namespace Database\Seeders;

use App\Models\SlotPrice;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class SlotPriceTableSeeder extends Seeder
{
    public function run(): void
    {
        $timeSlots = TimeSlot::all();
        
        // Đặt mốc giá cơ sở cho 1 TIẾNG (60 phút)
        $baseNormalPricePerHour = 100000; 
        $basePeakPricePerHour = 150000;

        foreach ($timeSlots as $slot) {
            $ratio = $slot->duration_minutes / 60; 
            $normalPrice = $baseNormalPricePerHour * $ratio;
            $peakPrice = $basePeakPricePerHour * $ratio;

            // Lấy giờ bắt đầu để xác định buổi tối (Từ 17:00 trở đi)
            $startHour = (int) substr($slot->start_time, 0, 2);
            $isEvening = $startHour >= 17;

            for ($dayOfWeek = 0; $dayOfWeek <= 6; $dayOfWeek++) {
                // Thứ 7, Chủ nhật (0, 6) HOẶC buổi tối ngày thường đều là Giờ vàng
                $isPeak = ($dayOfWeek === 0 || $dayOfWeek === 6 || $isEvening);
                
                $priceType = $isPeak ? 'peak' : 'normal';
                $price = $isPeak ? $peakPrice : $normalPrice;

                SlotPrice::create([
                    'time_slot_id' => $slot->id,
                    'price'        => $price,
                    'price_type'   => $priceType,
                    'day_of_week'  => $dayOfWeek,
                ]);
            }
        }
    }
}