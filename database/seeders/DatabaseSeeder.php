<?php

namespace Database\Seeders;

use App\Models\Setting; // Import Model Setting
use Database\Seeders\VietnamUnitsSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\SportsTableSeeder;
use Database\Seeders\OwnerRegistrationsTableSeeder;
use Database\Seeders\VenuesTableSeeder;
use Database\Seeders\CourtsTableSeeder;
use Database\Seeders\TimeSlotTableSeeder;
use Database\Seeders\SlotPriceTableSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Nạp các Settings mặc định cho mô hình Marketplace
        $financialSettings = [
            ['key' => 'default_commission_rate', 'value' => '10'], // 10%
            ['key' => 'owner_credit_limit', 'value' => '-1000000'], // Cho nợ tối đa 1 triệu
            ['key' => 'minimum_withdraw', 'value' => '200000'],     // Rút tối thiểu 200k
            ['key' => 'minimum_topup', 'value' => '50000'],         // Nạp tối thiểu 50k
        ];

        foreach ($financialSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        // 2. Gọi các Seeder hiện có
        $this->call([
            VietnamUnitsSeeder::class,
            UsersTableSeeder::class,
            SportsTableSeeder::class,
            OwnerRegistrationsTableSeeder::class,
            VenuesTableSeeder::class,
            CourtsTableSeeder::class,
            TimeSlotTableSeeder::class,
            SlotPriceTableSeeder::class,
        ]);
    }
}