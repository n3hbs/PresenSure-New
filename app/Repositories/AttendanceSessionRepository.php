<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Repositories\Interfaces\AttendanceSessionRepositoryInterface;

class AttendanceSessionRepository implements AttendanceSessionRepositoryInterface
{
    public function create(array $data): AttendanceSession
    {
        return AttendanceSession::create($data);
    }

    public function findActiveSession(int $schedule_id): ?AttendanceSession
    {
        return AttendanceSession::where('schedule_id', $schedule_id)
            ->whereDate('start_at', today())
            ->latest('start_at')
            ->first();
    }

    public function editAttendanceStatus(int $attendanceSession_id, array $data): int
    {
        return AttendanceSession::where('attendance_session_id', $attendanceSession_id)
            ->update($data);
    }

    public function findAttendanceSession(int $attendanceSession_id, int $schedule_id): ?AttendanceSession
    {
        return AttendanceSession::where('attendance_session_id', $attendanceSession_id)
            ->where('schedule_id', $schedule_id)
            ->first();
    }
}
