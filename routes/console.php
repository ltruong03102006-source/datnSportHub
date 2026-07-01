<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Nhớ có dòng này
use App\Models\MatchPost; // Nhớ có dòng này

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// --- CODE AUTO-EXPIRED CHẠY NGẦM CỦA BẠN Ở ĐÂY ---
Schedule::call(function () {
    MatchPost::where('status', 'open')
        ->where(function($query) {
            // Tình huống 1: Ngày đá đã là quá khứ (Hôm qua)
            $query->where('play_date', '<', now()->toDateString())
                  // Tình huống 2: Ngày đá là hôm nay, nhưng giờ đá đã qua
                  ->orWhere(function($sq) {
                      $sq->where('play_date', now()->toDateString())
                         ->where('play_time', '<', now()->format('H:i'));
                  });
        })->update(['status' => 'expired']);
})->everyMinute();
// --------------------------------------------------