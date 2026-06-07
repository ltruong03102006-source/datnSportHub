<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'court_id' => 'required|exists:courts,id',
            'slot_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required_without:slots|date_format:H:i',
            'end_time' => 'required_without:slots|date_format:H:i|after:start_time',
            'slots' => 'nullable|array|min:1',
            'slots.*.start_time' => 'required_with:slots|date_format:H:i',
            'slots.*.end_time' => 'required_with:slots|date_format:H:i|after:slots.*.start_time',
            'note' => 'nullable|string|max:500',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->route('courtId')) {
            $this->merge([
                'court_id' => $this->route('courtId'),
            ]);
        }
    }
}
