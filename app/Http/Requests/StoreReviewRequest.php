<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chấm sao (1–5).',
            'rating.min' => 'Số sao tối thiểu là 1.',
            'rating.max' => 'Số sao tối đa là 5.',
            'content.max' => 'Nội dung đánh giá tối đa 1000 ký tự.',
        ];
    }
}
