<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenuePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $venue = $this->route('venue');

        return $this->user()?->role === 'owner'
            && $venue
            && (int) $venue->owner_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in(['week', 'month']),
            ],

            'duration' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'week' && (int) $value > 52) {
                        $fail('Gói theo tuần không được vượt quá 52 tuần.');
                    }

                    if ($this->input('type') === 'month' && (int) $value > 24) {
                        $fail('Gói theo tháng không được vượt quá 24 tháng.');
                    }
                },
            ],

            'max_sessions_per_week' => [
                'required',
                'integer',
                'min:1',
                'max:7',
            ],

            'discount_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'max_subscribers' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên gói.',
            'name.max' => 'Tên gói không được vượt quá 255 ký tự.',

            'type.required' => 'Vui lòng chọn loại gói.',
            'type.in' => 'Loại gói không hợp lệ.',

            'duration.required' => 'Vui lòng nhập thời lượng gói.',
            'duration.integer' => 'Thời lượng gói phải là số nguyên.',
            'duration.min' => 'Thời lượng gói phải lớn hơn hoặc bằng 1.',

            'max_sessions_per_week.required' => 'Vui lòng chọn số buổi/tuần tối đa.',
            'max_sessions_per_week.integer' => 'Số buổi/tuần phải là số nguyên.',
            'max_sessions_per_week.min' => 'Số buổi/tuần tối thiểu là 1.',
            'max_sessions_per_week.max' => 'Số buổi/tuần tối đa là 7.',

            'discount_percent.required' => 'Vui lòng nhập phần trăm giảm giá.',
            'discount_percent.numeric' => 'Phần trăm giảm giá phải là số.',
            'discount_percent.min' => 'Phần trăm giảm giá không được nhỏ hơn 0.',
            'discount_percent.max' => 'Phần trăm giảm giá không được vượt quá 100.',

            'max_subscribers.integer' => 'Số lượng khách tối đa phải là số nguyên.',
            'max_subscribers.min' => 'Số lượng khách tối đa phải lớn hơn hoặc bằng 1.',
            'max_subscribers.max' => 'Số lượng khách tối đa không được vượt quá 10000.',

            'status.required' => 'Vui lòng chọn trạng thái gói.',
            'status.in' => 'Trạng thái gói không hợp lệ.',
        ];
    }
}