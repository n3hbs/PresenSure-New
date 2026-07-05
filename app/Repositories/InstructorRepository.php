<?php

namespace App\Repositories;

use App\Models\Instructor;
use App\Repositories\Interfaces\InstructorRepositoryInterface;

class InstructorRepository implements InstructorRepositoryInterface
{
    public function create(array $data)
    {
        return Instructor::create($data);
    }
}
