<?php

namespace App\Http\Resources;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
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
            'attendance_record_id' => $this->attendance_record_id,
            'attendance_session_id' => $this->attendance_session_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'presence_verified' => (bool) $this->presence_verified,
            'face_verified' => (bool) $this->face_verified,
            'face_verified_at' => $formatDate($this->face_verified_at),
            'verified_at' => $formatDate($this->verified_at),
            'created_at' => $formatDate($this->created_at),
            'updated_at' => $formatDate($this->updated_at),
        ];
    }
}
