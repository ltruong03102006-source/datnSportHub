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