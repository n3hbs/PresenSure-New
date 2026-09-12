<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateAttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendanceRecord = $this['attendance_record'] ?? $this['attendanceRecord'];
        $bleDetection = $this['ble_detection'] ?? $this['bleDetection'];

        return [
            'attendance_record' => new AttendanceRecordResource($attendanceRecord),
            'ble_detection' => new BleDetectionResource($bleDetection),
        ];
    }
}
