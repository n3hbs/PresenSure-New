<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    public function singleStudentRegistration(
        StoreStudentRequest $request
    ) {
        return response()->json(
            $this->studentService->registerStudent(
                $request->validated()
            )
        );
    }
}
