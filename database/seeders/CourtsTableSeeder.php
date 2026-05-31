<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class CourtsTableSeeder extends Seeder
{
    public function run(): void
    {
        $venues = Venue::all();

        foreach ($venues as $venue) {
            for ($c = 1; $c <= 3; $c++) {
                Court::create([
                    'venue_id' => $venue->id,
                    'name' => "Sân con $c",
                    'status' => 'active',
                    'is_bookable_online' => true,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
