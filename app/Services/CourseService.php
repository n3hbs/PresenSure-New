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
}
