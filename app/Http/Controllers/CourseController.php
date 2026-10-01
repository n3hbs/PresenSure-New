<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Course\AssignUserCourseBlockRequest;
use App\Http\Requests\Course\CreateCourseBlockRequest;
use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    /**
     * Display a listing of active courses.
     */
    public function index(): JsonResponse
    {
        $courses = $this->courseService->getAllCourses();

        return $this->successResponse(
            CourseResource::collection($courses),
            'Courses retrieved successfully.',
            200
        );
    }

    /**
     * Store a newly created course.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse($request->validated());

        return $this->successResponse(
            new CourseResource($course),
            'Course created successfully.',
            201
        );
    }

    /**
     * Display the specified course with active-semester course blocks.
     */
    public function show(int $course_id): JsonResponse
    {
        $course = $this->courseService->getCourseById($course_id);

        if (! $course) {
            return $this->errorResponse('Course not found.', 404);
        }

        return $this->successResponse(
            new CourseResource($course),
            'Course retrieved successfully.',
            200
        );
    }

    /**
     * Update the specified course.
     */
    public function update(UpdateCourseRequest $request, int $course_id): JsonResponse
    {
        $course = $this->courseService->updateCourse($course_id, $request->validated());

        return $this->successResponse(
            new CourseResource($course),
            'Course updated successfully.',
            200
        );
    }

    /**
     * Archive (soft-delete) the specified course.
     */
    public function destroy(int $course_id): JsonResponse
    {
        $this->courseService->deleteCourse($course_id);

        return $this->successResponse(
            null,
            'Course archived successfully.',
            200
        );
    }

    /**
     * Display a listing of archived courses.
     */
    public function archives(): JsonResponse
    {
        $archived = $this->courseService->getArchivedCourses();

        return $this->successResponse(
            CourseResource::collection($archived),
            'Archived courses retrieved successfully.',
            200
        );
    }

    /**
     * Restore an archived course.
     */
    public function restore(int $course_id): JsonResponse
    {
        $restored = $this->courseService->restoreCourse($course_id);

        return $this->successResponse(
            new CourseResource($restored),
            'Course restored successfully.',
            200
        );
    }

    /**
     * Legacy course creation endpoint.
     */
    public function create(CreateCourseRequest $request): JsonResponse
    {
        $this->courseService->createCourse($request->validated());

        return response()->json([
            'message' => 'Course Successfully Created',
        ], 201);
    }

    /**
     * Create a single course block.
     */
    public function createBlock(CreateCourseBlockRequest $request): JsonResponse
    {
        $this->courseService->createCourseBlock($request->validated());

        return response()->json([
            'message' => 'Course block successfully created.',
        ], 201);
    }

    /**
     * Assign users to a course block.
     */
    public function assign(AssignUserCourseBlockRequest $request): JsonResponse
    {
        $this->courseService->assignUsersToCourseBlock($request->validated());

        return response()->json([
            'message' => 'Users successfully assigned to course block.',
        ], 201);
    }
}
