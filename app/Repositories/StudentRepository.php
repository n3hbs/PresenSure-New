<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Interfaces\StudentRepositoryInterface;

class StudentRepository implements StudentRepositoryInterface
{
    private function activeSemesterStudentQuery(int $semesterId)
    {
        return User::with([
            'student' => function ($query) use ($semesterId) {
                $query->orderByRaw('CASE WHEN semester_id = ? THEN 0 ELSE 1 END', [$semesterId])
                    ->orderByDesc('created_at')
                    ->with('program.department');
            },
            'roleAssignment.role',
            'userProfile',
        ])
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
            ->orderByDesc('created_at')
            ->get();
    }

    public function getStudentDetails(string $user_id, int $semesterId)
    {
        return $this->activeSemesterStudentQuery($semesterId)
            ->with([
                'userCourseBlocks' => function ($query) use ($semesterId) {
                    $query->whereHas('courseBlock', function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    })->with([
                        'courseBlock.course',
                        'courseBlock.semester',
                        'courseBlock.schedules.scheduleDays',
                        'courseBlock.schedules.room.building',
                    ]);
                },
            ])
            ->where('user_id', $user_id)
            ->first();
    }
}
