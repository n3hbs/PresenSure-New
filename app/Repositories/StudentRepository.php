<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Override;

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

    public function getStudentByActiveSemester(int $semesterId)
    {
        $student = User::with('student.program.department','roleAssignment.role', 'userProfile')
            ->whereHas('student', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->whereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'student');
            })
            ->get();

        return $student;
    }
}
