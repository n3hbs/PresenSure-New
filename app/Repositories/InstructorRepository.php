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
            ->where(function ($query) {
                $query->whereHas('instructor', function ($q) {
                    $q->where('status', 'Active');
                })->orWhere(function ($sub) {
                    $sub->whereHas('roleAssignment.role', function ($r) {
                        $r->where('role_name', 'instructor');
                    })->whereDoesntHave('instructor');
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function getInstructorDetails(string $userId, ?int $semesterId = null)
    {
        return User::with([
            'instructor.department',
            'roleAssignment.role',
            'userProfile',
            'userCourseBlocks' => function ($query) use ($semesterId) {
                if ($semesterId) {
                    $query->whereHas('courseBlock', function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    });
                }
                $query->with([
                    'courseBlock.course',
                    'courseBlock.semester',
                    'courseBlock.schedules.scheduleDays',
                    'courseBlock.schedules.room.building',
                ]);
            },
        ])
            ->where('user_id', $userId)
            ->first();
    }

    public function archiveInstructor(string $userId): bool
    {
        $updated = Instructor::where('user_id', $userId)
            ->update(['status' => 'Inactive']);

        return (bool) $updated;
    }

    public function updateInstructor(string $userId, array $data): bool
    {
        $instructor = Instructor::where('user_id', $userId)->first();
        if ($instructor) {
            return (bool) $instructor->update($data);
        }

        return false;
    }

    public function restoreInstructor(string $userId): bool
    {
        $updated = Instructor::where('user_id', $userId)
            ->update(['status' => 'Active']);

        return (bool) $updated;
    }

    public function getArchivedInstructors()
    {
        return User::with([
            'instructor.department',
            'roleAssignment.role',
            'userProfile',
        ])
            ->whereHas('instructor', function ($q) {
                $q->where('status', 'Inactive');
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
