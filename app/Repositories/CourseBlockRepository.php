<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CourseBlock;
use App\Repositories\Interfaces\CourseBlockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseBlockRepository implements CourseBlockRepositoryInterface
{
    /**
     * Find an active course block by ID.
     */
    public function findById(int $id): ?CourseBlock
    {
        return CourseBlock::find($id);
    }

    /**
     * Find an archived (soft-deleted) course block by ID.
     */
    public function findTrashedById(int $id): ?CourseBlock
    {
        return CourseBlock::onlyTrashed()->find($id);
    }

    /**
     * Find a course block with counts for schedules and user course blocks.
     */
    public function findByIdWithCounts(int $id): ?CourseBlock
    {
        return CourseBlock::withCount(['schedules', 'userCourseBlocks'])->find($id);
    }

    /**
     * Update an existing course block.
     */
    public function update(int $id, array $data): CourseBlock
    {
        $block = CourseBlock::findOrFail($id);
        $block->update($data);

        return $block;
    }

    /**
     * Archive (soft-delete) a course block.
     */
    public function delete(int $id): bool
    {
        $block = CourseBlock::findOrFail($id);

        return (bool) $block->delete();
    }

    /**
     * Restore an archived course block.
     */
    public function restore(int $id): CourseBlock
    {
        $block = CourseBlock::onlyTrashed()->findOrFail($id);
        $block->restore();

        return $block;
    }

    /**
     * Get archived course blocks with filters.
     *
     * @return Collection<int, CourseBlock>
     */
    public function getArchived(?int $courseId = null, ?string $search = null): Collection
    {
        $query = CourseBlock::onlyTrashed()
            ->with([
                'course:course_id,subject_code,name',
                'semester.schoolYear',
                'instructor:user_id,first_name,last_name',
            ]);

        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('block_code', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($cq) use ($search) {
                        $cq->where('subject_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('instructor', function ($iq) use ($search) {
                        $iq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('deleted_at')->get();
    }

    /**
     * Synchronize instructor assignment in user_course_blocks table.
     */
    public function syncInstructorUserCourseBlock(CourseBlock $block, ?string $instructorId): void
    {
        $block->userCourseBlocks()
            ->where(function ($q) {
                $q->whereHas('user.roleAssignment.role', function ($r) {
                    $r->where('role_name', 'instructor');
                })->orWhereHas('user.instructor');
            })->delete();

        if ($instructorId) {
            $block->userCourseBlocks()->create([
                'user_id' => $instructorId,
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Get all active course blocks with filters and eager loaded relations.
     *
     * @return Collection<int, CourseBlock>
     */
    public function getAllActive(?int $courseId = null, ?int $semesterId = null, ?string $search = null): Collection
    {
        $query = CourseBlock::whereHas('course')
            ->with([
                'course:course_id,subject_code,name,description,deleted_at',
                'semester.schoolYear',
                'instructor:user_id,first_name,last_name,email',
                'schedules.scheduleDays',
                'schedules.room.building',
                'userCourseBlocks.user.roleAssignment.role',
                'userCourseBlocks.user.student.program',
                'userCourseBlocks.user.userProfile',
                'userCourseBlocks.user.instructor',
            ]);

        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }

        if ($semesterId !== null) {
            $query->where('semester_id', $semesterId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('block_code', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($cq) use ($search) {
                        $cq->where('subject_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('instructor', function ($iq) use ($search) {
                        $iq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('block_code')->get();
    }

    /**
     * Find active course block by ID with relations.
     */
    public function findActiveById(int $id): ?CourseBlock
    {
        return CourseBlock::where('course_block_id', $id)
            ->whereHas('course')
            ->with([
                'course:course_id,subject_code,name,description,deleted_at',
                'semester.schoolYear',
                'instructor:user_id,first_name,last_name,email',
                'schedules.scheduleDays',
                'schedules.room.building',
                'userCourseBlocks.user.roleAssignment.role',
                'userCourseBlocks.user.student.program',
                'userCourseBlocks.user.userProfile',
                'userCourseBlocks.user.instructor',
            ])
            ->first();
    }
}
