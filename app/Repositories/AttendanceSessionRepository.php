<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\User;
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

    public function findById(int $attendanceSessionId): ?AttendanceSession
    {
        return AttendanceSession::find($attendanceSessionId);
    }

    public function getSessionStudentsWithAttendance(int $scheduleId, int $attendanceSessionId)
    {
        $schedule = Schedule::findOrFail($scheduleId);

        return User::with([
            'userProfile',
            'student.program',
            'roleAssignment.role',
            'attendanceRecords' => function ($query) use ($attendanceSessionId) {
                $query->where('attendance_session_id', $attendanceSessionId);
            },
        ])
            ->whereHas('userCourseBlocks', function ($query) use ($schedule) {
                $query->where('course_block_id', $schedule->course_block_id);
            })
            ->whereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'student');
            })
            ->orderByRaw("CASE WHEN LOWER(sex) = 'male' THEN 1 WHEN LOWER(sex) = 'female' THEN 2 ELSE 3 END")
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->get();
    }
}
