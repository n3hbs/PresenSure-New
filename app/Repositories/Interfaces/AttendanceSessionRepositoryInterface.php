<?php

namespace App\Repositories\Interfaces;

use App\Models\AttendanceSession;

interface AttendanceSessionRepositoryInterface
{
    public function create(array $data): AttendanceSession;

    public function findActiveSession(int $schedule_id): ?AttendanceSession;

    public function editAttendanceStatus(int $attendanceSession_id, array $data): int;

    public function findAttendanceSession(int $attendanceSession_id, int $schedule_id): ?AttendanceSession;

    public function findById(int $attendanceSessionId): ?AttendanceSession;

    public function getSessionStudentsWithAttendance(int $scheduleId, int $attendanceSessionId);
}
