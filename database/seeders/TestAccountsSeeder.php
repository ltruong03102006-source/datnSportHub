<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin Account
        User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin Test',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'status' => 'active',
            'balance' => 0,
        ]);

        // 2. Owner Account
        User::updateOrCreate([
            'email' => 'owner@gmail.com',
        ], [
            'name' => 'Chủ Sân Test',
            'password' => Hash::make('123456'),
            'role' => 'owner',
            'status' => 'active',
            'balance' => 0,
        ]);

        // 3. User Account
        User::updateOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'name' => 'Khách Hàng Test',
            'password' => Hash::make('123456'),
            'role' => 'user',
            'status' => 'active',
            'balance' => 10000000, // 10 triệu để test thanh toán
        ]);
    }
}
