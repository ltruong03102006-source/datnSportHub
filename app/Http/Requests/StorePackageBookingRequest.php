<?php

namespace App\Http\Requests;

use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\VenuePackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePackageBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'package_id' => [
                'required',
                'integer',
                'exists:venue_packages,id',
            ],

            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'weekly_sessions' => [
                'nullable',
                'integer',
                'min:1',
                'max:7',
            ],

            'sessions' => [
                'required',
                'array',
                'min:1',
                'max:7',
            ],

            'sessions.*.court_id' => [
                'required',
                'integer',
                'exists:courts,id',
            ],

            'sessions.*.time_slot_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'sessions.*.time_slot_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:time_slots,id',
            ],

            'sessions.*.weekday' => [
                'required',
                'integer',
                'between:0,6',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sessions = collect($this->input('sessions', []))
            ->map(function ($session) {
                if (! is_array($session)) {
                    return $session;
                }

                if (! isset($session['time_slot_ids']) && isset($session['time_slot_id'])) {
                    $session['time_slot_ids'] = [$session['time_slot_id']];
                }

                if (isset($session['time_slot_ids']) && ! is_array($session['time_slot_ids'])) {
                    $session['time_slot_ids'] = [$session['time_slot_ids']];
                }

                if (isset($session['time_slot_ids'])) {
                    $session['time_slot_ids'] = collect($session['time_slot_ids'])
                        ->filter(fn ($value) => $value !== null && $value !== '')
                        ->map(fn ($value) => (int) $value)
                        ->values()
                        ->all();
                }

                return $session;
            })
            ->values()
            ->all();

        $this->merge(['sessions' => $sessions]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $package = VenuePackage::query()
                    ->with('venue')
                    ->find($this->input('package_id'));

                $sessions = collect($this->input('sessions', []))
                    ->values();

                if (! $package || $sessions->isEmpty()) {
                    return;
                }

                if (! $package->venue?->allow_package_booking) {
                    $validator->errors()->add(
                        'package_id',
                        'Cơ sở sân chưa bật chức năng đặt theo gói.'
                    );
                }

                if ($package->status !== 'active') {
                    $validator->errors()->add(
                        'package_id',
                        'Gói đặt sân hiện không hoạt động.'
                    );
                }

                $maxSessionsPerWeek = (int) ($package->max_sessions_per_week ?: 7);

                if ($sessions->count() > $maxSessionsPerWeek) {
                    $validator->errors()->add(
                        'sessions',
                        "Gói này chỉ cho phép tối đa {$maxSessionsPerWeek} buổi/tuần."
                    );
                }

                if ($this->filled('weekly_sessions') && (int) $this->input('weekly_sessions') !== $sessions->count()) {
                    $validator->errors()->add(
                        'weekly_sessions',
                        'Số buổi/tuần không khớp với số buổi đã chọn.'
                    );
                }

                $duplicateKeys = $sessions->map(function (array $session) {
                    $slotIds = collect($session['time_slot_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->sort()
                        ->values()
                        ->implode(',');

                    return implode('-', [
                        $session['weekday'] ?? '',
                        $session['court_id'] ?? '',
                        $slotIds,
                    ]);
                });

                if ($duplicateKeys->unique()->count() !== $duplicateKeys->count()) {
                    $validator->errors()->add(
                        'sessions',
                        'Không được chọn trùng cùng thứ, cùng sân và cùng khung giờ trong một gói.'
                    );
                }

                if ($sessions->count() === 7) {
                    $weekdayCount = $sessions
                        ->pluck('weekday')
                        ->map(fn ($weekday) => (int) $weekday)
                        ->unique()
                        ->count();

                    if ($weekdayCount !== 7) {
                        $validator->errors()->add(
                            'sessions',
                            'Nếu chọn 7 buổi/tuần thì cần chọn đủ 7 ngày khác nhau.'
                        );
                    }
                }

                foreach ($sessions as $index => $session) {
                    $rowNumber = $index + 1;

                    $court = Court::query()
                        ->with('venue')
                        ->find($session['court_id'] ?? null);

                    $timeSlot = TimeSlot::query()
                        ->find($session['time_slot_id'] ?? null);

                    if (! $court || ! $timeSlot) {
                        continue;
                    }

                    if ((int) $package->venue_id !== (int) $court->venue_id) {
                        $validator->errors()->add(
                            "sessions.{$index}.court_id",
                            "Buổi {$rowNumber}: Sân không thuộc cơ sở của gói đã chọn."
                        );
                    }

                    if ((int) $timeSlot->court_id !== (int) $court->id) {
                        $validator->errors()->add(
                            "sessions.{$index}.time_slot_id",
                            "Buổi {$rowNumber}: Khung giờ không thuộc sân đã chọn."
                        );
                    }

                    if (method_exists($court, 'canBeBooked') && ! $court->canBeBooked()) {
                        $validator->errors()->add(
                            "sessions.{$index}.court_id",
                            "Buổi {$rowNumber}: Sân hiện không cho phép đặt online."
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Vui lòng chọn gói đặt sân.',
            'package_id.exists' => 'Gói đặt sân không tồn tại.',

            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'start_date.after_or_equal' => 'Ngày bắt đầu không được nhỏ hơn hôm nay.',

            'weekly_sessions.integer' => 'Số buổi/tuần phải là số nguyên.',
            'weekly_sessions.min' => 'Số buổi/tuần tối thiểu là 1.',
            'weekly_sessions.max' => 'Số buổi/tuần tối đa là 7.',

            'sessions.required' => 'Vui lòng chọn ít nhất 1 buổi trong tuần.',
            'sessions.array' => 'Danh sách buổi không hợp lệ.',
            'sessions.min' => 'Vui lòng chọn ít nhất 1 buổi trong tuần.',
            'sessions.max' => 'Chỉ được chọn tối đa 7 buổi/tuần.',

            'sessions.*.court_id.required' => 'Vui lòng chọn sân.',
            'sessions.*.court_id.exists' => 'Sân đã chọn không tồn tại.',

            'sessions.*.time_slot_id.required' => 'Vui lòng chọn khung giờ.',
            'sessions.*.time_slot_id.exists' => 'Khung giờ đã chọn không tồn tại.',

            'sessions.*.weekday.required' => 'Vui lòng chọn thứ trong tuần.',
            'sessions.*.weekday.between' => 'Thứ trong tuần không hợp lệ.',
        ];
    }
}
