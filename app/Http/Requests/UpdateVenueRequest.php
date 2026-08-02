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
            // 1. Thông tin cơ bản & Vị trí (Tab 1 & 2)
            'sport_id' => ['required', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'province_code' => ['required', 'string', 'exists:provinces,code'],
            'ward_code' => [
                'required',
                'string',
                Rule::exists('wards', 'code')->where('province_code', $this->input('province_code')),
            ],
            'description' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],

            // 2. Thông tin liên hệ (Bắt buộc & SĐT chỉ chứa số)
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'email' => ['required', 'email', 'max:255'],

            // 3. Hình ảnh cơ sở
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'deleted_image_ids' => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer'],

            // 4. Thông tin pháp lý & Ngân hàng (Tab 3)
            'owner_name' => ['required', 'string', 'max:255'],
            'citizen_id' => [
                'required', 
                'digits:12'
            ],
            'business_license_number' => [
                'nullable', // GPKD nên để nullable vì Hộ cá thể đôi khi không có, nếu dự án bắt buộc thì bạn đổi thành 'required'
                'alpha_num', 
                'max:50'
            ],
            
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],

            // 5. File đính kèm pháp lý (Chỉ validate định dạng nếu người dùng có up file MỚI)
            'citizen_front_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'citizen_back_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'business_license_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'rental_contract_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'land_certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không hợp lệ.',
            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'ward_code.exists' => 'Phường/xã không hợp lệ hoặc không thuộc tỉnh đã chọn.',
            
            // Thông báo bắt buộc & định dạng cho Liên hệ
            'phone.required' => 'Vui lòng nhập số điện thoại hotline.',
            'phone.regex' => 'Số điện thoại chỉ được phép chứa các chữ số.',
            'email.required' => 'Vui lòng nhập email liên hệ.',
            'email.email' => 'Email không đúng định dạng.',

            // Thông báo pháp lý
            'owner_name.required' => 'Vui lòng nhập tên chủ sở hữu.',
            'citizen_id.required' => 'Vui lòng nhập số Căn cước công dân.',
            'citizen_id.digits' => 'Số Căn cước công dân phải bao gồm chính xác 12 chữ số.',
            'business_license_number.required' => 'Vui lòng nhập mã số thuế hoặc số giấy phép kinh doanh.',
            'business_license_number.alpha_num' => 'Số giấy phép kinh doanh chỉ được chứa chữ cái và số.',
        ];
    }
}