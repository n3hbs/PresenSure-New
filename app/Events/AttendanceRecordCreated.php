<?php

namespace App\Events;

use App\Models\AttendanceRecord;
use App\Models\BleDetection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecordCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AttendanceRecord $attendanceRecord,
        public ?BleDetection $bleDetection = null
    ) {
        $this->attendanceRecord->loadMissing(['student.userProfile']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('attendance.session.' . $this->attendanceRecord->attendance_session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AttendanceRecordCreated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $student = $this->attendanceRecord->student;

        return [
            'attendance_record_id' => $this->attendanceRecord->attendance_record_id,
            'attendance_session_id' => $this->attendanceRecord->attendance_session_id,
            'student_id' => $this->attendanceRecord->student_id,
            'student' => $student ? [
                'user_id' => $student->user_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => trim("{$student->first_name} {$student->last_name}"),
                'profile_picture' => $student->userProfile?->profile_picture,
            ] : null,
            'presence_verified' => (bool) $this->attendanceRecord->presence_verified,
            'face_verified' => (bool) $this->attendanceRecord->face_verified,
            'face_verified_at' => $this->attendanceRecord->face_verified_at?->toIso8601String(),
            'verified_at' => $this->attendanceRecord->verified_at?->toIso8601String() ?? now()->toIso8601String(),
            'status' => $this->attendanceRecord->status,
            'rssi' => $this->bleDetection?->rssi,
        ];
    }
}
