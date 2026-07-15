<?php

namespace App\Repositories\Interfaces;

interface CourseRepositoryInterface
{
    public function create(array $data);

    public function createCourseBlock(array $data);

    public function assignUserToCourseBlock(array $data);
}
