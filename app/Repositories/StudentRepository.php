<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Interfaces\StudentRepositoryInterface;

class StudentRepository implements StudentRepositoryInterface
{
    public function create(array $data)
    {
        return Student::create($data);
    }
    public function isEnrolled(string $userId, int $semesterId): bool
    {
        return Student::where('user_id', $userId)
            ->where('semester_id', $semesterId)
            ->exists();
    }
}
