<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CourseBlock;
use App\Repositories\Interfaces\CourseBlockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class CourseBlockService
{
    public function __construct(
        protected CourseBlockRepositoryInterface $courseBlockRepository,
    ) {}

    /**
     * Update an existing course block inside a transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBlock(int $id, array $data): CourseBlock
    {
        return DB::transaction(function () use ($id, $data) {
            $block = $this->courseBlockRepository->findById($id);

            if (! $block) {
                throw new ModelNotFoundException("Course block with ID {$id} not found.");
            }

            $updateData = [
                'block_code' => $data['block_code'],
            ];

            if (array_key_exists('instructor_id', $data)) {
                $updateData['instructor_id'] = $data['instructor_id'];
            }

            $block->update($updateData);

            if (array_key_exists('instructor_id', $data)) {
                $this->courseBlockRepository->syncInstructorUserCourseBlock($block, $data['instructor_id'] ?? null);
            }

            $block->load(['instructor', 'semester.schoolYear']);

            return $block;
        });
    }

    /**
     * Archive (soft-delete) a course block with data integrity safeguards.
     */
    public function archiveBlock(int $id): CourseBlock
    {
        $block = $this->courseBlockRepository->findByIdWithCounts($id);

        if (! $block) {
            throw new ModelNotFoundException("Course block with ID {$id} not found.");
        }

        $reasons = [];

        // Check 1: Linked class schedules
        if ($block->schedules_count > 0) {
            $reasons[] = "{$block->schedules_count} class schedule(s)";
        }

        // Check 2: Linked users (students or instructors)
        if ($block->user_course_blocks_count > 0) {
            $reasons[] = "{$block->user_course_blocks_count} assigned user(s) or student(s)";
        }

        // Prevent archive if linked active data exists
        if (! empty($reasons)) {
            $details = implode(', ', $reasons);
            throw new HttpResponseException(response()->json([
                'message' => "Cannot archive course block '{$block->block_code}': it currently contains linked active data ({$details}). Please unassign all users and delete class schedules before archiving this block.",
                'errors' => [
                    'block' => ["Course block has linked active records: {$details}."],
                ],
            ], 422));
        }

        // Safe to soft-delete
        $this->courseBlockRepository->delete($id);

        return $block;
    }

    /**
     * View archived course blocks.
     *
     * @return Collection<int, CourseBlock>
     */
    public function getArchivedBlocks(?int $courseId = null, ?string $search = null): Collection
    {
        return $this->courseBlockRepository->getArchived($courseId, $search);
    }

    /**
     * Restore an archived course block.
     */
    public function restoreBlock(int $id): CourseBlock
    {
        $block = $this->courseBlockRepository->findTrashedById($id);

        if (! $block) {
            throw new ModelNotFoundException("Archived course block with ID {$id} not found.");
        }

        // Verify parent course is active
        if ($block->course()->onlyTrashed()->exists()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Cannot restore this course block because its parent course is archived. Please restore the course first.',
            ], 422));
        }

        $this->courseBlockRepository->restore($id);
        $block->load(['course', 'semester.schoolYear', 'instructor']);

        return $block;
    }

    /**
     * Get all active course blocks.
     *
     * @return Collection<int, CourseBlock>
     */
    public function getActiveBlocks(?int $courseId = null, ?int $semesterId = null, ?string $search = null): Collection
    {
        return $this->courseBlockRepository->getAllActive($courseId, $semesterId, $search);
    }

    /**
     * Get single active course block by ID.
     */
    public function getBlockById(int $id): ?CourseBlock
    {
        return $this->courseBlockRepository->findActiveById($id);
    }
}
