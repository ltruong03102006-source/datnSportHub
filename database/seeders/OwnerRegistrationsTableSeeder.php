<?php

namespace Database\Seeders;

use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerRegistrationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Hoàng Cường', 'Phạm Minh Đức',
            'Hoàng Thị Em', 'Vũ Quốc Phong', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Đỗ Mạnh Hùng', 'Ngô Thị Kim', 'Dương Văn Long', 'Lý Thị Mai',
        ];

        $prefixes = ['090', '091', '098', '097', '032', '077', '081', '084', '093', '070', '096', '089'];

        foreach ($names as $i => $fullName) {
            $n = $i + 1;

            $user = User::create([
                'name' => $fullName,
                'email' => "owneruser{$n}@example.com",
                'password' => bcrypt('password'),
                'role' => 'owner',
                'status' => 'active',
            ]);

            OwnerRegistration::create([
                'user_id' => $user->id,
                'name' => $fullName,
                'phone' => $prefixes[$i] . str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => "owner{$n}@example.com",
                'status' => 'active',
            ]);
        }
    }
}
