<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\AssignUserCourseBlockRequest;
use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Resources\UserCourseScheduleResource;
use App\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    public function create(CreateCourseRequest $request)
    {
        $this->courseService->createCourse($request->validated());
        return response()->json([
            'message' => 'Course Successfully Created',
        ], 201);
    }

    public function assign(AssignUserCourseBlockRequest $request)
    {
        $this->courseService->assignUsersToCourseBlock($request->validated());

        return response()->json([
            'message' => 'Users successfully assigned to course block.',
        ], 201);
    }

    public function getUserCourseSchedule(string $userId)
    {
        $schedules = $this->courseService->getUserCourseSchedule($userId);

        return response()->json([
            'data' => UserCourseScheduleResource::collection($schedules),
        ]);
    }
}
