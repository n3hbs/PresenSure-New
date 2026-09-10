<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attendance_session_id' => $this->attendance_session_id,
            'session_code' => $this->session_code,
            'schedule_id' => $this->schedule_id,
            'period_id' => $this->period_id,
            'instructor_id' => $this->instructor_id,
            'ble_device_id' => $this->ble_device_id,
            'verification_mode' => $this->verification_mode,
            'requires_periodic_verification' => (bool) $this->requires_periodic_verification,
            'status' => $this->status,
            'start_at' => $this->start_at?->toISOString() ?? $this->start_at,
            'end_at' => $this->end_at?->toISOString() ?? $this->end_at,
            'ble_token_expires_at' => $this->ble_token_expires_at?->toISOString() ?? $this->ble_token_expires_at,
            'device_started_at' => $this->device_started_at?->toISOString() ?? $this->device_started_at,
            'created_at' => $this->created_at?->toISOString() ?? $this->created_at,
            'updated_at' => $this->updated_at?->toISOString() ?? $this->updated_at,
        ];
    }
}
