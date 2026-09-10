<?php

namespace App\Http\Resources;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BleDetectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $formatDate = fn ($date) => $date instanceof CarbonInterface ? $date->toISOString() : $date;

        return [
            'ble_detection_id' => $this->ble_detection_id,
            'attendance_record_id' => $this->attendance_record_id,
            'user_id' => $this->user_id,
            'rssi' => $this->rssi,
            'detected_at' => $formatDate($this->detected_at),
        ];
    }
}
