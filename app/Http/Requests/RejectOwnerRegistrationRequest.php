<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectOwnerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Vui lòng nhập lý do từ chối.',
            'reason.string' => 'Lý do từ chối phải là chuỗi ký tự.',
            'reason.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ];
    }
}
