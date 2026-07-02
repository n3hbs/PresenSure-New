<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Services\StudentService;
use App\Http\Resources\Student\ActiveSemesterStudentListResource;
use App\Http\Resources\Student\StudentDetailsResource;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
    ) {}

    public function create(StoreStudentRequest $request)
    {
        return response()->json(
            $this->studentService->registerStudent(
                $request->validated()
            )
        );
    }

    public function getStudentByActiveSemester()
    {
        $students = $this->studentService->getStudentByActiveSemester();
        return ActiveSemesterStudentListResource::collection($students);
    }
    public function getStudentDetails(string $user_id)
    {
        $student = $this->studentService->getStudentDetails($user_id);
        return new StudentDetailsResource($student);
    }
}
