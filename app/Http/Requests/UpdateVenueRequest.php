<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return [
            'sport_id' => ['required', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'province_code' => ['required', 'string', 'exists:provinces,code'],
            'ward_code' => [
                'required',
                'string',
                // Ward must belong to the selected province
                Rule::exists('wards', 'code')->where('province_code', $this->input('province_code')),
            ],
            'description' => ['nullable', 'string'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'deleted_image_ids' => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không hợp lệ.',
            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'ward_code.exists' => 'Phường/xã không hợp lệ hoặc không thuộc tỉnh đã chọn.',
        ];
    }
}
