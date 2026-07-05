<?php

namespace App\Http\Controllers;

use App\Services\InstructorService;
use App\Http\Requests\Instructor\CreateInstructorRequest;

class InstructorController extends Controller
{
    public function __construct(
        protected InstructorService $instructorService,
    ) {}

    public function create(CreateInstructorRequest $request)
    {
        $this->instructorService->createInstructor($request->validated());
        return response()->json([
            'message' => 'Instructor successfully registered.',
        ], 201);
    }
}
