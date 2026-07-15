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
        return CourseBlock::firstOrCreate($data);
    }

    public function assignUserToCourseBlock(array $data)
    {
        return UserCourseBlock::firstOrCreate($data);
    }
}
