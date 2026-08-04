<?php

namespace App\Repositories\Interfaces;

interface AttendanceRecordRepositoryInterface
{
    public function create(array $data);

    public function getAttendanceRecord(int $attendance_record_id);
}
