<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportsTableSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['name' => 'Bóng đá', 'slug' => 'bong-da'],
            ['name' => 'Bóng rổ', 'slug' => 'bong-ro'],
            ['name' => 'Tennis', 'slug' => 'tennis'],
            ['name' => 'Cầu lông', 'slug' => 'cau-long'],
            ['name' => 'Bóng chuyền', 'slug' => 'bong-chuyen'],
            ['name' => 'Bóng bàn', 'slug' => 'bong-ban'],
        ];

        foreach ($sports as $s) {
            Sport::create(array_merge($s, ['icon' => null]));
        }
    }
}
