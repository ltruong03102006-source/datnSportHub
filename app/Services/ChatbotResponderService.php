<?php

namespace App\Services;

use App\Models\Venue;

class ChatbotResponderService
{
    public function reply(string $message, array $conversationHistory = []): array
    {
        $context = $this->buildContext($message, $conversationHistory);
        $normalized = $this->normalize($context);

        $venueSuggestion = $this->venueSuggestion($normalized);
        if ($venueSuggestion !== null) {
            return [
                'message' => $venueSuggestion,
                'intent' => 'search_help',
                'confidence' => 0.88,
            ];
        }

        foreach ($this->rules() as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return [
                        'message' => $rule['answer'],
                        'intent' => $rule['intent'],
                        'confidence' => 0.9,
                    ];
                }
            }
        }

        return [
            'message' => 'Mình chưa hiểu rõ câu hỏi này. Bạn có thể hỏi về đặt sân, thanh toán, hủy lịch, đổi lịch, đánh giá hoặc tìm sân gần bạn.',
            'intent' => 'fallback',
            'confidence' => 0.35,
        ];
    }

    private function buildContext(string $message, array $conversationHistory): string
    {
        $messages = array_values(array_filter(array_map(fn ($value) => trim((string) $value), $conversationHistory)));

        if (empty($messages)) {
            return $message;
        }

        $recent = array_slice($messages, -5);
        $recent[] = $message;

        return implode(' ', $recent);
    }

    private function venueSuggestion(string $normalized): ?string
    {
        if (! preg_match('/(tim san|tim kiem san|gan day|sân gan|tim san gan|tim kiem san gan|xem san|tim san the thao)/u', $normalized)) {
            return null;
        }

        $venues = Venue::query()
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'name', 'status']);

        if ($venues->isEmpty()) {
            return null;
        }

        $names = $venues->pluck('name')->filter()->values()->all();

        return 'Mình gợi ý một số sân phù hợp: ' . implode(', ', $names) . '. Bạn có thể chọn khu vực hoặc môn thể thao để xem sân còn trống và đặt lịch nhanh hơn.';
    }

    private function normalize(string $message): string
    {
        $message = mb_strtolower(trim($message), 'UTF-8');
        $map = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];

        return strtr($message, $map);
    }

    private function rules(): array
    {
        return [
            [
                'intent' => 'booking_help',
                'keywords' => ['dat san', 'dat lich', 'booking', 'chon san'],
                'answer' => 'Để đặt sân, bạn mở trang chi tiết cơ sở, chọn sân con, chọn ngày/khung giờ còn trống rồi xác nhận thanh toán.',
            ],
            [
                'intent' => 'payment_help',
                'keywords' => ['thanh toan', 'vnpay', 'qr', 'chuyen khoan', 'vi'],
                'answer' => 'SportHub hỗ trợ thanh toán theo luồng đặt sân. Sau khi chọn lịch, hệ thống sẽ hiển thị phương thức thanh toán phù hợp như ví, QR hoặc VNPay nếu được bật.',
            ],
            [
                'intent' => 'cancel_help',
                'keywords' => ['huy san', 'huy lich', 'hoan tien', 'phi phat'],
                'answer' => 'Bạn có thể vào Lịch sử đặt sân để hủy lịch. Phí hủy và hoàn tiền phụ thuộc chính sách của từng cơ sở và thời điểm hủy.',
            ],
            [
                'intent' => 'reschedule_help',
                'keywords' => ['doi lich', 'doi gio', 'doi ngay', 'reschedule'],
                'answer' => 'Để đổi lịch, vào Lịch sử đặt sân, chọn booking đủ điều kiện rồi gửi yêu cầu đổi lịch. Chủ sân sẽ duyệt hoặc từ chối yêu cầu.',
            ],
            [
                'intent' => 'review_help',
                'keywords' => ['danh gia', 'review', 'nhan xet'],
                'answer' => 'Sau khi booking hoàn thành, bạn có thể vào phần đánh giá để chấm sao và viết nhận xét cho cơ sở/sân đã sử dụng.',
            ],
            [
                'intent' => 'checkin_help',
                'keywords' => ['check in', 'check-in', 'khach den san', 'khong den'],
                'answer' => 'Chức năng check-in dành cho chủ sân. Chủ sân mở trang Check-in để đánh dấu khách đã đến hoặc không đến theo booking trong ngày.',
            ],
            [
                'intent' => 'package_help',
                'keywords' => ['goi dat san', 'goi tuan', 'goi thang', 'dat theo goi'],
                'answer' => 'Gói đặt sân giúp khách đặt khung giờ cố định theo tuần hoặc tháng. Nếu cơ sở bật gói, bạn sẽ thấy nút đặt theo gói ở trang chi tiết cơ sở.',
            ],
            [
                'intent' => 'account_help',
                'keywords' => ['tai khoan', 'dang nhap', 'dang ky', 'mat khau', 'ho so'],
                'answer' => 'Bạn có thể đăng nhập hoặc đăng ký ở góc trên trang. Sau khi đăng nhập, vào Trang cá nhân để đổi thông tin, avatar, mật khẩu và xem lịch sử.',
            ],
            [
                'intent' => 'owner_help',
                'keywords' => ['chu san', 'quan ly san', 'dang ky chu san', 'owner'],
                'answer' => 'Nếu bạn là chủ sân, hãy dùng khu vực đăng nhập chủ sân để quản lý cơ sở, lịch đặt, gói đặt sân, check-in và thanh toán.',
            ],
            [
                'intent' => 'search_help',
                'keywords' => ['tim san', 'gan day', 'noi bat', 'mon the thao', 'dia diem'],
                'answer' => 'Bạn có thể tìm sân theo môn thể thao, khu vực hoặc xem bảng nổi bật để chọn cơ sở có đánh giá và lượt đặt tốt.',
            ],
        ];
    }
}
