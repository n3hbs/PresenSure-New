<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository implements CourseRepositoryInterface
{
    public function create(array $data)
    {
        return Course::create($data);
    }

    public function createCourseBlock(array $data)
    {
        return CourseBlock::firstOrCreate(
            [
                'course_id' => $data['course_id'],
                'semester_id' => $data['semester_id'],
                'block_code' => $data['block_code'],
            ]
        );
    }

    public function assignUserToCourseBlock(array $data)
    {
        return UserCourseBlock::firstOrCreate(
            [
                'user_id' => $data['user_id'],
                'course_block_id' => $data['course_block_id'],
            ],
            [
                'assigned_at' => $data['assigned_at'] ?? now(),
            ]
        );
    }

    public function getUserCourseSchedule(string $userId, int $semesterId)
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
}
