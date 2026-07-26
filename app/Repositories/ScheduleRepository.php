<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\ScheduleRepositoryInterface;

final class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function create(array $data)
    {
        return Schedule::create($data);
    }

    public function createScheduleDay(array $data)
    {
        return ScheduleDay::create($data);
    }

    public function getUserScheduleBySemester(string $userId, int $semesterId)
    {
        return UserCourseBlock::with([
            'courseBlock.course',
            'courseBlock.semester',
            'courseBlock.schedules.scheduleDays',
            'courseBlock.schedules.room.building',
        ])
            ->where('user_id', $userId)
            ->whereHas('courseBlock', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->get();
    }

    public function findSchedule(int $schedule_id)
    {
        return Schedule::with('scheduleDays')
            ->findOrFail($schedule_id);
    }
}
