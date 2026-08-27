<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\CreateStudentRequest;
use App\Services\StudentService;
use App\Http\Resources\Student\ActiveSemesterStudentListResource;
use App\Http\Resources\Student\CheckStudentResource;
use App\Http\Resources\Student\StudentDetailsResource;
use App\Http\Resources\StudentResource;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
    ) {}

    public function create(CreateStudentRequest $request)
    {
        $student = $this->studentService->registerStudent($request->validated());

        return $this->successResponse(
            new StudentResource($student),
            'Student registered successfully.',
            201
        );
    }

    public function getStudentByActiveSemester()
    {
        $students = $this->studentService->getStudentByActiveSemester();
        return ActiveSemesterStudentListResource::collection($students)
            ->message('Student List Retrieved Successfully.')
            ->status(200);
    }

    public function getStudentDetails(string $user_id)
    {
        $student = $this->studentService->getStudentDetails($user_id);
        return (new StudentDetailsResource($student))
            ->message('Student Details Retrieved Successfully.')
            ->status(200);
    }

    public function checkStudent(string $user_id)
    {
        $student = $this->studentService->checkStudent($user_id);
        return (new CheckStudentResource($student))
            ->message('Student Checked Successfully.')
            ->status(200);
    }
}
