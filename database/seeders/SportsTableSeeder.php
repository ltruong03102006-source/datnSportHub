<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportsTableSeeder extends Seeder
{
    public function run(): void
    {
        $icon = 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/';

        $sports = [
            ['name' => 'Bóng đá', 'slug' => 'bong-da', 'icon' => $icon . '26bd.svg'],
            ['name' => 'Bóng rổ', 'slug' => 'bong-ro', 'icon' => $icon . '1f3c0.svg'],
            ['name' => 'Tennis', 'slug' => 'tennis', 'icon' => $icon . '1f3be.svg'],
            ['name' => 'Cầu lông', 'slug' => 'cau-long', 'icon' => $icon . '1f3f8.svg'],
            ['name' => 'Bóng chuyền', 'slug' => 'bong-chuyen', 'icon' => $icon . '1f3d0.svg'],
            ['name' => 'Bóng bàn', 'slug' => 'bong-ban', 'icon' => $icon . '1f3d3.svg'],
        ];

        foreach ($sports as $s) {
            Sport::create($s);
        }
    }
}
