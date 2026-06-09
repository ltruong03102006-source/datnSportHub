<?php

namespace Database\Seeders;

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
        // Chỉ gọi seeder tạo Users và Sports
        $this->call([
            UsersTableSeeder::class,
            SportsTableSeeder::class,
            
            // TẮT TOÀN BỘ SEEDER TẠO DỮ LIỆU GIẢ BÊN DƯỚI BẰNG CÁCH COMMENT (//)
            // OwnerRegistrationsTableSeeder::class,
            // VenuesTableSeeder::class,
            // CourtsTableSeeder::class,
            // TimeSlotTableSeeder::class,
            // SlotPriceTableSeeder::class,
        ]);
    }
}