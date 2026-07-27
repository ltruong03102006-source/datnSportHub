<?php

namespace Database\Seeders;

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
        $financialSettings = [
            ['key' => 'default_commission_rate', 'value' => '10'], 
            ['key' => 'owner_credit_limit', 'value' => '-1000000'], 
            ['key' => 'minimum_withdraw', 'value' => '200000'],     
            ['key' => 'minimum_topup', 'value' => '50000'],         
        ];

        foreach ($financialSettings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
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