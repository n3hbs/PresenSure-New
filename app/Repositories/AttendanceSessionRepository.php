<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\AttendanceSessionRepositoryInterface;
use Override;

class AttendanceSessionRepository implements AttendanceSessionRepositoryInterface
{
    public function create(array $data)
    {
        return AttendanceSession::create($data);
    }

    public function hasActiveSession(int $schedule_id): bool
    {
        return AttendanceSession::where('schedule_id', $schedule_id)
            ->where('status', 'active')
            ->exists();
    }

    public function findScheduleForSession(int $schedule_id)
    {
        return Schedule::with('scheduleDays')
            ->findOrFail($schedule_id);
    }

    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool
    {
        return UserCourseBlock::where('user_id', $user_id)
        ->where('course_block_id', $course_block_id)
        ->exists();
    }
}
