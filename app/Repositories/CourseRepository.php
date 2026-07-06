<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository implements CourseRepositoryInterface
{
    public function create(array $data)
    {
        return Course::create($data);
    }

    public function createCourseBlock(array $data)
    {
        return CourseBlock::create($data);
    }
}
