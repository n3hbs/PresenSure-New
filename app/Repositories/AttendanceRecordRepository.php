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

    public function getAttendanceRecord(int $schedule_id, string $student_id)
    {
        return AttendanceRecord::whereHas('attendanceSession', function ($query) use ($schedule_id) {
            $query->where('schedule_id', $schedule_id);
        })
            ->where('student_id', $student_id)
            ->whereDate('verified_at', today())
            ->first();
    }

}
