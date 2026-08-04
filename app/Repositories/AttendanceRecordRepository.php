<?php

namespace App\Repositories;

use App\Models\AttendanceRecord;
use App\Repositories\Interfaces\AttendanceRecordRepositoryInterface;

class AttendanceRecordRepository implements AttendanceRecordRepositoryInterface
{
    public function create(array $data)
    {
        return AttendanceRecord::create($data);
    }

    public function getAttendanceRecord(int $attendance_record_id)
    {
        return AttendanceRecord::where('attendance_record_id', $attendance_record_id)
            ->whereDate('verified_at', today())
            ->latest('verified_at')
            ->first();
    }
}
