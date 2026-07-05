<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CreateCourseRequest;
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
}
