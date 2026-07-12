<?php

namespace App\Services;

use App\Repositories\CourseRepository;

class CourseService
{
    public function __construct(
        protected CourseRepository $courseRepository
    ) {}

    public function createCourse(array $data){
        $this->courseRepository->create([
            'subject_code' => $data['subject_code'],
            'name' => $data['name']
        ]);
    }

    public function createCourseBlock(array $data)
    {
        return $this->courseRepository->createCourseBlock([
            'course_id' => $data['course_id'],
            'semester_id' => $data['semester_id'],
            'block_code' => $data['block_code'],
        ]);
    }

    public function assignUserToCourseBlock(array $data)
    {
        return $this->courseRepository->assignUserToCourseBlock([
            'user_id' => $data['user_id'],
            'course_block_id' => $data['course_block_id'],
            'assigned_at' => $data['assigned_at'] ?? now(),
        ]);
    }

    public function assignUsersToCourseBlock(array $data): void
    {
        foreach (array_unique($data['user_ids']) as $userId) {
            $this->assignUserToCourseBlock([
                'user_id' => $userId,
                'course_block_id' => $data['course_block_id'],
                'assigned_at' => $data['assigned_at'] ?? now(),
            ]);
        }
    }

    public function getUserCourseSchedule(string $userId)
    {
        return $this->courseRepository->getUserCourseSchedule($userId);
    }
}
