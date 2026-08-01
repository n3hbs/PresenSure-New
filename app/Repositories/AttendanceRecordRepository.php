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
}
