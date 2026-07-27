<?php

namespace App\Repositories\Interfaces;

use App\Models\BleDevice;

interface AttendanceSessionRepositoryInterface
{
    public function create(array $data);

    public function findActiveSession(int $schedule_id);

    public function editAttendanceStatus(int $attendanceSession_id, array $data);

    public function findAttendanceSession(int $attendanceSession_id, int $schedule_id);
}
