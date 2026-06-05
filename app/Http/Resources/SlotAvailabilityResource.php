<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlotAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slot_id' => $this['slot_id'],
            'court_id' => $this['court_id'],
            'start_time' => substr($this['start_time'], 0, 5),
            'end_time' => substr($this['end_time'], 0, 5),
            'duration_minutes' => $this['duration_minutes'],
            'price' => (int)$this['price'],
            'price_type' => $this['price_type'],
            'is_available' => (bool)$this['is_available'],
        ];
    }
}
