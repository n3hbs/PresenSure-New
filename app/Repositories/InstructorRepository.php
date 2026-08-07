<?php

namespace App\Repositories;

use App\Models\Instructor;
use App\Models\User;
use App\Repositories\Interfaces\InstructorRepositoryInterface;

class InstructorRepository implements InstructorRepositoryInterface
{
    public function create(array $data)
    {
        return Instructor::create($data);
    }

    public function getAllInstructors()
    {
        return User::with([
            'instructor.department',
            'roleAssignment.role',
            'userProfile',
        ])
            ->whereHas('instructor')
            ->orWhereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'instructor');
            })
            ->get();
    }
}
