<?php

namespace App\Http\Resources\AttendanceSession;

use App\Http\Resources\RoleResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionStudentAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendanceRecord = $this->attendanceRecords?->first();

        return [
            'user' => new UserResource($this),

            'student' => StudentResource::collection(
                $this->whenLoaded('student')
            ),

            'role' => new RoleResource(
                $this->whenLoaded('roleAssignment')?->role
            ),

            'profile' => new UserProfileResource(
                $this->whenLoaded('userProfile')
            ),

            'attendance_record' => $attendanceRecord ? [
                'attendance_record_id' => $attendanceRecord->attendance_record_id,
                'attendance_session_id' => $attendanceRecord->attendance_session_id,
                'status' => $attendanceRecord->status,
                'presence_verified' => (bool) $attendanceRecord->presence_verified,
                'face_verified' => (bool) $attendanceRecord->face_verified,
                'face_verified_at' => $attendanceRecord->face_verified_at,
                'verified_at' => $attendanceRecord->verified_at,
            ] : null,

            'attendance_status' => $attendanceRecord ? $attendanceRecord->status : 'unmarked',
        ];
    }
}
