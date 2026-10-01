<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\UserCourseBlock;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    /**
     * Get all active courses with counts.
     *
     * @return Collection<int, Course>
     */
    public function getAll(): Collection;

    /**
     * Get all archived (soft-deleted) courses.
     *
     * @return Collection<int, Course>
     */
    public function getArchived(): Collection;

    /**
     * Find a course by ID with optional active semester course blocks.
     */
    public function findById(int $id, bool $withActiveSemesterBlocks = true): ?Course;

    /**
     * Create a new course with optional course blocks.
     */
    public function create(array $data): Course;

    /**
     * Update an existing course and its course blocks.
     */
    public function update(int $id, array $data): Course;

    /**
     * Soft-delete a course.
     */
    public function delete(int $id): bool;

    /**
     * Restore an archived course.
     */
    public function restore(int $id): Course;

    /**
     * Check if a course has dependencies preventing deletion.
     *
     * @return array<int, string>
     */
    public function hasDependencies(int $id): array;

    /**
     * Create a single course block (legacy compatibility).
     */
    public function createCourseBlock(array $data): CourseBlock;

    /**
     * Assign a user to a course block (legacy compatibility).
     */
    public function assignUserToCourseBlock(array $data): UserCourseBlock;
}
