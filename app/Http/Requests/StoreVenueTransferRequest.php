<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\VenueTransferRequest;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreVenueTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Phải là chủ của cái sân này mới được chuyển nhượng
        return $this->user()->id === $this->route('venue')->owner_id;
    }

    public function rules(): array
    {
        return [
            'receiver_email' => [
                'required',
                'email',
                function (string $attribute, mixed $value, Closure $fail) {
                    // Kiểm tra tài khoản nhận có tồn tại và là role owner không
                    $receiver = User::where('email', $value)->where('role', 'owner')->first();
                    
                    if (!$receiver) {
                        $fail('Không tìm thấy tài khoản Chủ sân nào trùng khớp với Email này.');
                        return;
                    }
                    if ($receiver->id === auth()->id()) {
                        $fail('Bạn không thể chuyển nhượng cơ sở cho chính mình.');
                    }
                },
            ],
        ];
    }

    // Hook kiểm tra mở rộng: Công nợ và Trạng thái chờ
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $venue = $this->route('venue');

            // 1. Kiểm tra xem sân có đang bị pending chuyển nhượng không
            $hasPending = VenueTransferRequest::where('venue_id', $venue->id)
                ->where('status', 'pending')
                ->exists();
                
            if ($hasPending) {
                $validator->errors()->add('venue', 'Cơ sở này đang có một yêu cầu chuyển nhượng chờ Admin duyệt, không thể tạo thêm.');
            }

            // 2. Kiểm tra công nợ của Chủ A
            // $wallet = auth()->user()->wallet;
            // if ($wallet && $wallet->debt > 0) {
            //     $validator->errors()->add('debt', 'Hệ thống từ chối: Ví của bạn đang ghi nhận khoản nợ ' . number_format($wallet->debt, 0, ',', '.') . 'đ. Vui lòng thanh toán công nợ trước khi chuyển nhượng.');
            // }
        });
    }
}