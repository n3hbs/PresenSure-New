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
        return User::with([
            'student' => function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId)
                    ->where('status', 'Active')
                    ->orderByDesc('created_at')
                    ->with('program.department');
            },
            'roleAssignment.role',
            'userProfile',
        ])
            ->whereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'student');
            })
            ->whereHas('student', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId)
                    ->where('status', 'Active');
            })
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

    public function updateStudent(string $userId, int $semesterId, array $data)
    {
        $student = Student::where('user_id', $userId)
            ->where('semester_id', $semesterId)
            ->first();

        if ($student) {
            $student->update($data);

            return $student;
        }

        $latest = Student::where('user_id', $userId)->latest()->first();
        if ($latest) {
            $latest->update($data);

            return $latest;
        }

        return null;
    }

    public function archiveStudent(string $userId, int $semesterId): bool
    {
        $updated = Student::where('user_id', $userId)
            ->where('semester_id', $semesterId)
            ->update(['status' => 'Inactive']);

        if (! $updated) {
            $updated = Student::where('user_id', $userId)
                ->update(['status' => 'Inactive']);
        }

        return (bool) $updated;
    }

    public function deleteStudent(string $userId, ?int $semesterId = null): bool
    {
        $query = Student::where('user_id', $userId);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        return (bool) $query->delete();
    }

    public function getArchivedStudents(?int $semesterId = null)
    {
        return User::with([
            'student' => function ($query) use ($semesterId) {
                if ($semesterId) {
                    $query->where('semester_id', $semesterId);
                }
                $query->where('status', 'Inactive')
                    ->orderByDesc('created_at')
                    ->with('program.department');
            },
            'roleAssignment.role',
            'userProfile',
        ])
            ->whereHas('roleAssignment.role', function ($query) {
                $query->where('role_name', 'student');
            })
            ->whereHas('student', function ($query) use ($semesterId) {
                if ($semesterId) {
                    $query->where('semester_id', $semesterId);
                }
                $query->where('status', 'Inactive');
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function restoreStudent(string $userId, ?int $semesterId = null): bool
    {
        $query = Student::where('user_id', $userId);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $updated = $query->update(['status' => 'Active']);

        if (! $updated) {
            $updated = Student::where('user_id', $userId)
                ->update(['status' => 'Active']);
        }

        return (bool) $updated;
    }
}
