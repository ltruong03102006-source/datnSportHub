<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Sport;
use App\Models\OwnerRegistration;
use Illuminate\Database\Seeder;

class VenuesTableSeeder extends Seeder
{
    public function run(): void
    {
        $sports = Sport::all();
        $owners = OwnerRegistration::all();

        if ($sports->isEmpty() || $owners->isEmpty()) {
            return;
        }

        foreach ($sports as $sport) {
            // create 2 venues per sport
            for ($v = 1; $v <= 2; $v++) {
                $owner = $owners->random();

                Venue::create([
                    'owner_id' => $owner->user_id,
                    'sport_id' => $sport->id,
                    'name' => "Sân {$sport->name} $v",
                    'address' => "Sân {$sport->name} - Địa chỉ $v, Quận X",
                    'lat' => null,
                    'lng' => null,
                    'description' => null,
                    'banner' => null,
                    'status' => 'active',
                ]);
            }
        }
    }
}
