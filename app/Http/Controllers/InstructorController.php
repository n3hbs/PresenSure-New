<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\CreateInstructorRequest;
use App\Http\Resources\Instructor\InstructorListResource;
use App\Http\Resources\InstructorResource;
use App\Services\InstructorService;

class InstructorController extends Controller
{
    public function __construct(
        protected InstructorService $instructorService,
    ) {}

    public function create(CreateInstructorRequest $request)
    {
        $instructor = $this->instructorService->createInstructor($request->validated());
        return $this->successResponse(
            new InstructorResource($instructor),
            'Instructor successfully registered.',
            201
        );
    }

    public function getAll()
    {
        $instructors = $this->instructorService->getAllInstructors();
        return InstructorListResource::collection($instructors)
            ->message('Instructors successfully retrieved.')
            ->status(200);
    }
}
