<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . now()->toDateString(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Vui lòng nhập ngày',
            'date.date_format' => 'Định dạng ngày không đúng (Y-m-d)',
            'date.after_or_equal' => 'Không thể xem lịch trong quá khứ',
        ];
    }
}
