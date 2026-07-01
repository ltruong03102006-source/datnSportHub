<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'owner';
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
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'open_hours' => ['nullable', 'date_format:H:i'],
            'close_hours' => ['nullable', 'date_format:H:i'],
            'google_maps_address' => ['required', 'string', 'max:500'],
            'banner' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'owner_name' => ['required', 'string', 'max:255'],
            'citizen_id' => ['required', 'string', 'max:50'],
            'business_license_number' => ['required', 'string', 'max:100'],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'citizen_front_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'citizen_back_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'business_license_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'rental_contract_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'land_certificate_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
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
