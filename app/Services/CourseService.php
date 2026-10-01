<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public function __construct(
        protected CourseRepositoryInterface $courseRepository,
    ) {}

    /**
     * Get all active courses with counts.
     *
     * @return Collection<int, Course>
     */
    public function getAllCourses(): Collection
    {
        return $this->courseRepository->getAll();
    }

    /**
     * Get all archived (soft-deleted) courses.
     *
     * @return Collection<int, Course>
     */
    public function getArchivedCourses(): Collection
    {
        return $this->courseRepository->getArchived();
    }

    /**
     * Get course by ID with active semester course blocks.
     */
    public function getCourseById(int $id): ?Course
    {
        return $this->courseRepository->findById($id, true);
    }

    /**
     * Create a new course inside a transaction.
     */
    public function createCourse(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            return $this->courseRepository->create($data);
        });
    }

    /**
     * Update a course inside a transaction.
     */
    public function updateCourse(int $id, array $data): Course
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->courseRepository->update($id, $data);
        });
    }

    /**
     * Soft-delete a course with dependency checks.
     */
    public function deleteCourse(int $id): bool
    {
        $dependencies = $this->courseRepository->hasDependencies($id);

        if (! empty($dependencies)) {
            throw ValidationException::withMessages([
                'course' => [
                    'Cannot archive this course: '.implode(' ', $dependencies),
                ],
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->courseRepository->delete($id);
        });
    }

    /**
     * Restore an archived course inside a transaction.
     */
    public function restoreCourse(int $id): Course
    {
        return DB::transaction(function () use ($id) {
            return $this->courseRepository->restore($id);
        });
    }

    /**
     * Create course block (legacy compatibility).
     */
    public function createCourseBlock(array $data): CourseBlock
    {
        return $this->courseRepository->createCourseBlock([
            'course_id' => $data['course_id'],
            'semester_id' => $data['semester_id'],
            'block_code' => $data['block_code'],
        ]);
    }

    /**
     * Assign user to course block (legacy compatibility).
     */
    public function assignUserToCourseBlock(array $data): UserCourseBlock
    {
        return $this->courseRepository->assignUserToCourseBlock([
            'user_id' => $data['user_id'],
            'course_block_id' => $data['course_block_id'],
            'assigned_at' => $data['assigned_at'] ?? now(),
        ]);
    }

    /**
     * Assign multiple users to course block (legacy compatibility).
     */
    public function assignUsersToCourseBlock(array $data): void
    {
        foreach (array_unique($data['user_ids']) as $userId) {
            $this->assignUserToCourseBlock([
                'user_id' => $userId,
                'course_block_id' => $data['course_block_id'],
                'assigned_at' => $data['assigned_at'] ?? now(),
            ]);
        }
    }
}
