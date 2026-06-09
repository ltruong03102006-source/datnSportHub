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
            $this->command->warn('Vui lòng chạy OwnerRegistrationsTableSeeder và SportsTableSeeder trước!');
            return;
        }

        $banners = $this->banners();

        foreach ($this->venues() as $slug => $venues) {
            $sportId = $sportIds[$slug] ?? null;

            if (! $sportId) {
                continue;
            }

            $pool = $banners[$slug] ?? [];

            // Thêm 2 biến $lat và $lng vào mảng phân rã (destructuring array)
            foreach ($venues as $i => [$name, $address, $lat, $lng]) {
                Venue::create([
                    'owner_id' => $owners->random()->user_id,
                    'sport_id' => $sportId,
                    'name' => $name,
                    'address' => $address,
                    'lat' => $lat, // Cập nhật vĩ độ
                    'lng' => $lng, // Cập nhật kinh độ
                    'description' => 'Đây là mô tả mẫu cho sân thể thao.',
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
        // Cấu trúc mảng mới: ['Tên Sân', 'Địa chỉ', Vĩ độ, Kinh độ]
        return [
            'bong-da' => [
                ['Sân Bóng Đá Chảo Lửa', '123 Lê Văn Lương, Thanh Xuân, Hà Nội', 21.0028, 105.8052],
                ['Sân Cỏ Nhân Tạo Mỹ Đình', '5 Lê Đức Thọ, Nam Từ Liêm, Hà Nội', 21.0315, 105.7651],
                ['Sân Bóng Đá Thành Đồng', '78 Nguyễn Trãi, Quận 5, TP.HCM', 10.7562, 106.6775],
                ['Sân Bóng Đá Ngôi Sao', 'Times City, Hai Bà Trưng, Hà Nội', 20.9959, 105.8677],
                ['Sân Bóng Phú Nhuận', '210 Phan Đăng Lưu, Phú Nhuận, TP.HCM', 10.8016, 106.6806],
            ],
            'bong-ro' => [
                ['Nhà Thi Đấu Phan Đình Phùng', '8 Võ Văn Tần, Quận 3, TP.HCM', 10.7766, 106.6918],
                ['Sân Bóng Rổ Hoàng Cầu', '12 Hoàng Cầu, Đống Đa, Hà Nội', 21.0197, 105.8239],
                ['CLB Bóng Rổ Saigon Heat', 'CityLand Park Hills, Gò Vấp, TP.HCM', 10.8358, 106.6669],
                ['Sân Bóng Rổ Cầu Giấy', '144 Xuân Thủy, Cầu Giấy, Hà Nội', 21.0371, 105.7816],
            ],
            'tennis' => [
                ['Sân Tennis Lan Anh', '291 Cách Mạng Tháng 8, Quận 10, TP.HCM', 10.7770, 106.6793],
                ['CLB Tennis Hồ Tây', '614 Lạc Long Quân, Tây Hồ, Hà Nội', 21.0717, 105.8078],
                ['Sân Tennis Rạng Đông', '2 Tôn Thất Tùng, Đống Đa, Hà Nội', 21.0022, 105.8288],
                ['Sân Tennis Thảo Điền', '12 Nguyễn Văn Hưởng, TP. Thủ Đức', 10.8072, 106.7323],
            ],
            'cau-long' => [
                ['Nhà Thi Đấu Trần Hưng Đạo', '1 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội', 21.0189, 105.8569],
                ['CLB Cầu Lông Ánh Dương', '45 Nguyễn Thị Minh Khai, Quận 1, TP.HCM', 10.7844, 106.6983],
                ['Sân Cầu Lông Sportzone', 'E2 Dương Đình Nghệ, Cầu Giấy, Hà Nội', 21.0264, 105.7891],
                ['Sân Cầu Lông Hoa Lư', '2 Đinh Tiên Hoàng, Quận 1, TP.HCM', 10.7876, 106.7001],
            ],
            'bong-chuyen' => [
                ['Nhà Thi Đấu Quân Khu 7', '202 Hoàng Văn Thụ, Tân Bình, TP.HCM', 10.7984, 106.6663],
                ['Sân Bóng Chuyền Bách Khoa', '1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội', 21.0069, 105.8431],
                ['CLB Bóng Chuyền Biển Xanh', '70 Nguyễn Huệ, Quận 1, TP.HCM', 10.7744, 106.7042],
            ],
            'bong-ban' => [
                ['CLB Bóng Bàn Hà Nội T&T', '229 Tây Sơn, Đống Đa, Hà Nội', 21.0088, 105.8202],
                ['Sân Bóng Bàn Thống Nhất', '138 Đào Duy Từ, Quận 10, TP.HCM', 10.7610, 106.6666],
                ['CLB Bóng Bàn Ngôi Sao Nhỏ', '15 Trần Phú, Hà Đông, Hà Nội', 20.9818, 105.7865],
                ['Sân Bóng Bàn Phú Mỹ Hưng', 'Phú Mỹ Hưng, Quận 7, TP.HCM', 10.7302, 106.7077],
            ],
        ];
    }
}