<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\CreateInstructorRequest;
use App\Http\Requests\Instructor\UpdateInstructorRequest;
use App\Http\Resources\Instructor\InstructorDetailsResource;
use App\Http\Resources\Instructor\InstructorListResource;
use App\Http\Resources\InstructorResource;
use App\Services\InstructorService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function getInstructorDetails(string $user_id)
    {
        $instructor = $this->instructorService->getInstructorDetails($user_id);

        return (new InstructorDetailsResource($instructor))
            ->message('Instructor Details Retrieved Successfully.')
            ->status(200);
    }

    public function update(UpdateInstructorRequest $request, ?string $user_id = null)
    {
        $userId = $user_id ?? $request->input('user_id');
        if (! $userId) {
            throw ValidationException::withMessages([
                'user_id' => ['The instructor ID (user_id) is required.'],
            ]);
        }

        $instructor = $this->instructorService->updateInstructor($userId, $request->validated());

        return (new InstructorDetailsResource($instructor))
            ->message('Instructor updated successfully.')
            ->status(200);
    }

    public function archive(string $user_id)
    {
        $this->instructorService->archiveInstructor($user_id);

        return $this->successResponse(
            null,
            'Instructor archived successfully.',
            200
        );
    }

    public function delete(Request $request, string $user_id)
    {
        $this->instructorService->deleteInstructor($user_id);

        return $this->successResponse(
            null,
            'Instructor archived successfully.',
            200
        );
    }

    public function restore(string $user_id)
    {
        $this->instructorService->restoreInstructor($user_id);

        return $this->successResponse(
            null,
            'Instructor restored successfully.',
            200
        );
    }

    public function getArchivedInstructors()
    {
        $instructors = $this->instructorService->getArchivedInstructors();

        return InstructorListResource::collection($instructors)
            ->message('Archived instructors successfully retrieved.')
            ->status(200);
    }
}
