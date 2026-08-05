<?php

namespace App\Repositories\Interfaces;

interface AttendanceRecordRepositoryInterface
{
    public function create(array $data);

    public function getAttendanceRecord(int $schedule_id, string $user_id);
}
