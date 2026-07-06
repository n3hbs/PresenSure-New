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
}
