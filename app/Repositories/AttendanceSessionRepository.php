<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\AttendanceSessionRepositoryInterface;
use Override;

class AttendanceSessionRepository implements AttendanceSessionRepositoryInterface
{

    public function create(array $data)
    {
        return AttendanceSession::create($data);
    }

    public function findActiveSession(int $schedule_id)
    {
        $now = now();
        return AttendanceSession::where('schedule_id', $schedule_id)
            ->where('status', 'active')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)
            ->first();
    }

    public function editAttendanceStatus(int $attendanceSession_id, array $data)
    {
        return AttendanceSession::where('attendance_session_id', $attendanceSession_id)
            ->update($data);
    }

    public function findAttendanceSession(int $attendanceSession_id, int $schedule_id)
    {
        $now = now();

        return AttendanceSession::query()
            ->where('attendance_session_id', $attendanceSession_id)
            ->where('schedule_id', $schedule_id)
            ->whereDate('start_at', $now->toDateString())
            ->where('start_at', '<=', $now)
            ->first();
    }
}
