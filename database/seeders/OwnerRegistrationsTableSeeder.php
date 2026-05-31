<?php

namespace Database\Seeders;

use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerRegistrationsTableSeeder extends Seeder
{
    public function run(): void
    {
        // create several owners to be assigned to venues
        for ($i = 1; $i <= 12; $i++) {
            $user = User::create([
                'name' => "OwnerUser {$i}",
                'email' => "owneruser{$i}@example.com",
                'password' => bcrypt('password'),
                'role' => 'owner',
                'status' => 'active',
            ]);

            OwnerRegistration::create([
                'user_id' => $user->id,
                'name' => "Owner $i",
                'phone' => '077523' . str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT),
                'email' => "owner{$i}@example.com",
                'status' => 'active',
            ]);
        }
    }
}
