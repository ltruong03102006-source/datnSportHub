<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreVenueTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $venue = $this->route('venue') ?? Venue::find($this->input('venue_id'));
        return $venue && $this->user()->id === (int) $venue->owner_id;
    }

    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'exists:venues,id'],
            'sender_owner_name' => ['required', 'string', 'max:255'],
            'sender_dob' => ['required', 'date', 'before:today'],
            'sender_address' => ['required', 'string', 'max:255'],
            'receiver_email' => [
                'required',
                'email',
                function (string $attribute, mixed $value, Closure $fail) {
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
            'price' => ['required', 'numeric', 'min:0'],
            'contract_date' => ['required', 'date'],
            'contract_location' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'venue_id.required' => 'Vui lòng chọn cơ sở cần chuyển nhượng.',
            'venue_id.exists' => 'Cơ sở đã chọn không hợp lệ.',
            'sender_owner_name.required' => 'Vui lòng nhập tên bên chuyển nhượng.',
            'sender_dob.required' => 'Vui lòng chọn ngày sinh của bên chuyển nhượng.',
            'sender_dob.date' => 'Ngày sinh bên chuyển nhượng không hợp lệ.',
            'sender_dob.before' => 'Ngày sinh phải là một ngày trong quá khứ.',
            'sender_address.required' => 'Vui lòng nhập chỗ ở hiện tại của bên chuyển nhượng.',
            'receiver_email.required' => 'Vui lòng nhập Email bên nhận.',
            'receiver_email.email' => 'Địa chỉ Email không đúng định dạng.',
            'price.required' => 'Vui lòng nhập giá chuyển nhượng.',
            'price.numeric' => 'Giá chuyển nhượng phải là số.',
            'price.min' => 'Giá chuyển nhượng không được nhỏ hơn 0.',
            'contract_date.required' => 'Vui lòng nhập ngày tạo hợp đồng.',
            'contract_date.date' => 'Ngày tạo hợp đồng không đúng định dạng ngày tháng.',
            'contract_location.required' => 'Vui lòng nhập địa điểm lập hợp đồng.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $venueId = $this->input('venue_id') ?? optional($this->route('venue'))->id;
            
            if ($venueId) {
                $hasPending = VenueTransferRequest::where('venue_id', $venueId)
                    ->whereIn('status', ['draft', 'sent', 'pending', 'filled', 'signed', 'pending_admin'])
                    ->exists();
                    
                if ($hasPending) {
                    $validator->errors()->add('venue_id', 'Cơ sở này đang có một yêu cầu chuyển nhượng chờ xử lý, không thể tạo thêm.');
                }
            }
        });
    }
}