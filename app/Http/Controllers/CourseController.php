<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\AssignUserCourseBlockRequest;
use App\Http\Requests\Course\CreateCourseBlockRequest;
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

    public function createBlock(CreateCourseBlockRequest $request)
    {
        $this->courseService->createCourseBlock($request->validated());
        return response()->json(['message' => 'Course block successfully created.',], 201);
    }

    public function assign(AssignUserCourseBlockRequest $request)
    {
        $this->courseService->assignUsersToCourseBlock($request->validated());
        return response()->json(['message' => 'Users successfully assigned to course block.',], 201);
    }
}
