<?php

namespace App\Services;

class ChatbotResponderService
{
    public function reply(string $message): array
    {
        $normalized = $this->normalize($message);

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
                'intent' => 'search_help',
                'keywords' => ['tim san', 'gan day', 'noi bat', 'mon the thao', 'dia diem'],
                'answer' => 'Bạn có thể tìm sân theo môn thể thao, khu vực hoặc xem bảng nổi bật để chọn cơ sở có đánh giá và lượt đặt tốt.',
            ],
        ];
    }
}
