<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Override;

class StudentRepository implements StudentRepositoryInterface
{
    private function activeSemesterStudentQuery(int $semesterId)
    {
        return User::with([
            'student.program.department',
            'roleAssignment.role',
            'userProfile',
        ])
            ->whereHas('student', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->whereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'student');
            });
    }
    
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
        return $this->activeSemesterStudentQuery($semesterId)
            ->get();
    }

    public function getStudentDetails(string $user_id, int $semesterId)
    {
        return $this->activeSemesterStudentQuery($semesterId)
            ->where('user_id', $user_id)
            ->first();
    }
}
