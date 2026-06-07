<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class CourtsTableSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            ['Sân 1', 'Sân 2', 'Sân 3', 'Sân 4', 'Sân 5'],
            ['Sân A', 'Sân B', 'Sân C', 'Sân D'],
            ['Sân VIP', 'Sân Thường 1', 'Sân Thường 2', 'Sân Ngoài Trời'],
            ['Sân Số 1', 'Sân Số 2', 'Sân Số 3'],
        ];

        foreach (Venue::all() as $venue) {
            $names = $styles[$venue->id % count($styles)];
            $count = rand(2, count($names));

            for ($i = 0; $i < $count; $i++) {
                Court::create([
                    'venue_id' => $venue->id,
                    'name' => $names[$i],
                    'status' => 'active',
                    'is_bookable_online' => true,
                    'created_at' => now()->subDays(rand(0, 45)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
