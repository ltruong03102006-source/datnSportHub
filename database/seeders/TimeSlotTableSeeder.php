<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TimeSlotTableSeeder extends Seeder
{
    public function run(): void
    {
        // Load danh sách sân kèm theo môn thể thao của nó
        $courts = Court::with('venue.sport')->get();

        foreach ($courts as $court) {
            $sportName = $court->venue?->sport?->name ?? '';

            // 1. Thuật toán xác định thời lượng ca dựa vào môn thể thao
            if (in_array($sportName, ['Cầu lông', 'Bóng bàn'])) {
                $slotDuration = 30; // 30 phút/ca
            } elseif ($sportName === 'Bóng đá') {
                $slotDuration = 90; // 90 phút/ca
            } else {
                $slotDuration = 60; // 60 phút/ca cho Tennis, Bóng rổ, v.v.
            }

            // 2. Bắt đầu sinh ca từ 06:00 đến 22:00
            $currentTime = Carbon::createFromTime(6, 0, 0);
            $endTimeLimit = Carbon::createFromTime(22, 0, 0);

            while ($currentTime < $endTimeLimit) {
                $slotEndTime = $currentTime->copy()->addMinutes($slotDuration);

                // Nếu ca được cộng thêm bị lố qua 22:00 thì ngắt vòng lặp (Tránh ca 21:00 - 22:30)
                if ($slotEndTime > $endTimeLimit) {
                    break;
                }

                TimeSlot::create([
                    'court_id'         => $court->id,
                    'start_time'       => $currentTime->format('H:i:s'),
                    'end_time'         => $slotEndTime->format('H:i:s'),
                    'duration_minutes' => $slotDuration,
                ]);

                // Tịnh tiến thời gian lên ca tiếp theo
                $currentTime->addMinutes($slotDuration);
            }
        }
    }
}