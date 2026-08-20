<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
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

    public function getScheduleStudentList(int $schedule_id)
    {
        $schedule = Schedule::findOrFail($schedule_id);

        return User::with([
            'userProfile',
            'student.program',
            'roleAssignment.role',
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
