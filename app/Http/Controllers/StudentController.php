<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\CreateStudentRequest;
use App\Services\StudentService;
use App\Http\Resources\Student\ActiveSemesterStudentListResource;
use App\Http\Resources\Student\CheckStudentResource;
use App\Http\Resources\Student\StudentDetailsResource;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
    ) {}

    public function create(CreateStudentRequest $request)
    {
        $this->studentService->registerStudent($request->validated());
        return response()->json(['message' => 'Student successfully registered.',], 201);
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

    public function checkStudent(string $user_id)
    {
        return new CheckStudentResource($this->studentService->checkStudent($user_id));
    }
}
