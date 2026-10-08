<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCourseBlockRequest;
use App\Services\CourseBlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseBlockController extends Controller
{
    public function __construct(
        protected CourseBlockService $courseBlockService
    ) {}

    /**
     * Update an existing Course Block
     * PUT /api/course-block/{id} or PUT /api/course-blocks/{id}
     */
    public function update(UpdateCourseBlockRequest $request, int|string $id): JsonResponse
    {
        $block = $this->courseBlockService->updateBlock((int) $id, $request->validated());

        return response()->json([
            'message' => 'Course block updated successfully.',
            'data' => $block,
        ], 200);
    }

    /**
     * Archive (Soft Delete) Course Block with Data Integrity Safeguards
     * DELETE /api/course-block/{id} or DELETE /api/course-blocks/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $block = $this->courseBlockService->archiveBlock((int) $id);

        return response()->json([
            'message' => "Course block '{$block->block_code}' has been archived successfully.",
        ], 200);
    }

    /**
     * View Archived Course Blocks
     * GET /api/courses/{course}/block-archives or GET /api/course-blocks/archives
     */
    public function archives(Request $request, int|string|null $course = null): JsonResponse
    {
        $courseId = $course !== null ? (int) $course : ($request->query('course_id') ? (int) $request->query('course_id') : null);
        $search = $request->query('search');

        $archivedBlocks = $this->courseBlockService->getArchivedBlocks(
            $courseId,
            $search !== null ? (string) $search : null
        );

        return response()->json([
            'data' => $archivedBlocks,
        ], 200);
    }

    /**
     * Restore an Archived Course Block
     * POST /api/course-block/{id}/restore or POST /api/course-blocks/{id}/restore
     */
    public function restore(int|string $id): JsonResponse
    {
        $block = $this->courseBlockService->restoreBlock((int) $id);

        return response()->json([
            'message' => "Course block '{$block->block_code}' has been restored successfully.",
            'data' => $block,
        ], 200);
    }
}
