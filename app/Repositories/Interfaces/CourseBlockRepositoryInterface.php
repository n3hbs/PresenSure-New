<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\CourseBlock;
use Illuminate\Database\Eloquent\Collection;

interface CourseBlockRepositoryInterface
{
    /**
     * Find an active course block by ID.
     */
    public function findById(int $id): ?CourseBlock;

    /**
     * Find an archived (soft-deleted) course block by ID.
     */
    public function findTrashedById(int $id): ?CourseBlock;

    /**
     * Find a course block with counts for schedules and user course blocks.
     */
    public function findByIdWithCounts(int $id): ?CourseBlock;

    /**
     * Update an existing course block.
     */
    public function update(int $id, array $data): CourseBlock;

    /**
     * Archive (soft-delete) a course block.
     */
    public function delete(int $id): bool;

    /**
     * Restore an archived course block.
     */
    public function restore(int $id): CourseBlock;

    /**
     * Get archived course blocks with filters.
     *
     * @return Collection<int, CourseBlock>
     */
    public function getArchived(?int $courseId = null, ?string $search = null): Collection;

    /**
     * Synchronize instructor assignment in user_course_blocks table.
     */
    public function syncInstructorUserCourseBlock(CourseBlock $block, ?string $instructorId): void;
}
