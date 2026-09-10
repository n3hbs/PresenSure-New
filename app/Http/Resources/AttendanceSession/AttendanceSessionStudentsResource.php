<?php

namespace App\Http\Resources\AttendanceSession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSessionStudentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = $this['session'];

        return [
            'session_id' => $session->attendance_session_id,
            'schedule_id' => $session->schedule_id,
            'session_status' => $session->status,
            'summary' => $this['summary'],
            'students' => SessionStudentAttendanceResource::collection($this['students']),
        ];
    }
}
