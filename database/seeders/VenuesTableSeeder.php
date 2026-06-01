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
        $owners = OwnerRegistration::all();
        $sportIds = Sport::pluck('id', 'slug');

        if ($owners->isEmpty() || $sportIds->isEmpty()) {
            return;
        }

        $banners = $this->banners();

        foreach ($this->venues() as $slug => $venues) {
            $sportId = $sportIds[$slug] ?? null;

            if (! $sportId) {
                continue;
            }

            $pool = $banners[$slug] ?? [];

            foreach ($venues as $i => [$name, $address]) {
                Venue::create([
                    'owner_id' => $owners->random()->user_id,
                    'sport_id' => $sportId,
                    'name' => $name,
                    'address' => $address,
                    'lat' => null,
                    'lng' => null,
                    'description' => null,
                    'banner' => $pool ? $pool[$i % count($pool)] : null,
                    'status' => 'active',
                ]);
            }
        }
    }

    private function banners(): array
    {
        $img = fn (string $id) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=640&q=80";

        return [
            'bong-da' => array_map($img, ['1551958219-acbc608c6377', '1431324155629-1a6deb1dec8d', '1459865264687-595d652de67e']),
            'bong-ro' => array_map($img, ['1546519638-68e109498ffc', '1608245449230-4ac19066d2d0', '1574623452334-1e0ac2b3ccb4']),
            'tennis' => array_map($img, ['1554068865-24cecd4e34b8', '1622279457486-62dcc4a431d6', '1595435934249-5df7ed86e1c0']),
            'cau-long' => array_map($img, ['1521587760476-6c12a4b040da', '1626224583764-f87db24ac4ea', '1613918431703-aa50889e3be9']),
            'bong-chuyen' => array_map($img, ['1612872087720-bb876e2e67d1', '1592656094267-764a45160876']),
            'bong-ban' => array_map($img, ['1534158914592-062992fbe900', '1611251135345-18c56206b863']),
        ];
    }

    private function venues(): array
    {
        return [
            'bong-da' => [
                ['Sân Bóng Đá Chảo Lửa', '123 Lê Văn Lương, Thanh Xuân, Hà Nội'],
                ['Sân Cỏ Nhân Tạo Mỹ Đình', '5 Lê Đức Thọ, Nam Từ Liêm, Hà Nội'],
                ['Sân Bóng Đá Thành Đồng', '78 Nguyễn Trãi, Quận 5, TP.HCM'],
                ['Sân Bóng Đá Ngôi Sao', 'Times City, Hai Bà Trưng, Hà Nội'],
                ['Sân Bóng Phú Nhuận', '210 Phan Đăng Lưu, Phú Nhuận, TP.HCM'],
            ],
            'bong-ro' => [
                ['Nhà Thi Đấu Phan Đình Phùng', '8 Võ Văn Tần, Quận 3, TP.HCM'],
                ['Sân Bóng Rổ Hoàng Cầu', '12 Hoàng Cầu, Đống Đa, Hà Nội'],
                ['CLB Bóng Rổ Saigon Heat', 'CityLand Park Hills, Gò Vấp, TP.HCM'],
                ['Sân Bóng Rổ Cầu Giấy', '144 Xuân Thủy, Cầu Giấy, Hà Nội'],
            ],
            'tennis' => [
                ['Sân Tennis Lan Anh', '291 Cách Mạng Tháng 8, Quận 10, TP.HCM'],
                ['CLB Tennis Hồ Tây', '614 Lạc Long Quân, Tây Hồ, Hà Nội'],
                ['Sân Tennis Rạng Đông', '2 Tôn Thất Tùng, Đống Đa, Hà Nội'],
                ['Sân Tennis Thảo Điền', '12 Nguyễn Văn Hưởng, TP. Thủ Đức'],
            ],
            'cau-long' => [
                ['Nhà Thi Đấu Trần Hưng Đạo', '1 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội'],
                ['CLB Cầu Lông Ánh Dương', '45 Nguyễn Thị Minh Khai, Quận 1, TP.HCM'],
                ['Sân Cầu Lông Sportzone', 'E2 Dương Đình Nghệ, Cầu Giấy, Hà Nội'],
                ['Sân Cầu Lông Hoa Lư', '2 Đinh Tiên Hoàng, Quận 1, TP.HCM'],
            ],
            'bong-chuyen' => [
                ['Nhà Thi Đấu Quân Khu 7', '202 Hoàng Văn Thụ, Tân Bình, TP.HCM'],
                ['Sân Bóng Chuyền Bách Khoa', '1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội'],
                ['CLB Bóng Chuyền Biển Xanh', '70 Nguyễn Huệ, Quận 1, TP.HCM'],
            ],
            'bong-ban' => [
                ['CLB Bóng Bàn Hà Nội T&T', '229 Tây Sơn, Đống Đa, Hà Nội'],
                ['Sân Bóng Bàn Thống Nhất', '138 Đào Duy Từ, Quận 10, TP.HCM'],
                ['CLB Bóng Bàn Ngôi Sao Nhỏ', '15 Trần Phú, Hà Đông, Hà Nội'],
                ['Sân Bóng Bàn Phú Mỹ Hưng', 'Phú Mỹ Hưng, Quận 7, TP.HCM'],
            ],
        ];
    }
}
