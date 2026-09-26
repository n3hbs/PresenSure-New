<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Semester\StoreSemesterRequest;
use App\Http\Requests\Semester\UpdateSemesterRequest;
use App\Http\Resources\SchoolYearResource;
use App\Http\Resources\SemesterResource;
use App\Services\SemesterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService,
    ) {}

    /**
     * Display a listing of semesters.
     */
    public function index(): JsonResponse
    {
        $semesters = $this->semesterService->getAllSemesters();

        return $this->successResponse(
            SemesterResource::collection($semesters),
            'Semesters retrieved successfully.',
            200
        );
    }

    /**
     * Store a newly created semester (with optional periods).
     */
    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $semester = $this->semesterService->createSemester($request->validated());

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester created successfully.',
            201
        );
    }

    /**
     * Display the specified semester.
     */
    public function show(int $semester_id): JsonResponse
    {
        $semester = $this->semesterService->getSemesterById($semester_id);

        if (! $semester) {
            return $this->errorResponse('Semester not found.', 404);
        }

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester retrieved successfully.',
            200
        );
    }

    /**
     * Update the specified semester.
     */
    public function update(UpdateSemesterRequest $request, int $semester_id): JsonResponse
    {
        $semester = $this->semesterService->updateSemester($semester_id, $request->validated());

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester updated successfully.',
            200
        );
    }

    /**
     * Remove the specified semester.
     */
    public function destroy(int $semester_id): Response
    {
        $this->semesterService->deleteSemester($semester_id);

        return response()->noContent();
    }

    /**
     * Set a semester as active.
     */
    public function setActive(int $semester_id): JsonResponse
    {
        $semester = $this->semesterService->setActiveSemester($semester_id);

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester activated successfully.',
            200
        );
    }

    /**
     * Get the active semester.
     */
    public function getActiveSemester(): JsonResponse
    {
        $semester = $this->semesterService->getActiveSemester();

        return $this->successResponse(
            new SemesterResource($semester),
            'Active semester retrieved successfully.',
            200
        );
    }

    /**
     * Get all school years.
     */
    public function schoolYears(): JsonResponse
    {
        $schoolYears = $this->semesterService->getAllSchoolYears();

        return $this->successResponse(
            SchoolYearResource::collection($schoolYears),
            'School years retrieved successfully.',
            200
        );
    }

    /**
     * Get all archived (soft-deleted) semesters.
     */
    public function archives(): JsonResponse
    {
        $semesters = $this->semesterService->getArchivedSemesters();

        return $this->successResponse(
            SemesterResource::collection($semesters),
            'Archived semesters retrieved successfully.',
            200
        );
    }

    /**
     * Restore an archived (soft-deleted) semester.
     */
    public function restore(int $semester_id): JsonResponse
    {
        $semester = $this->semesterService->restoreSemester($semester_id);

        return $this->successResponse(
            new SemesterResource($semester),
            'Semester restored successfully.',
            200
        );
    }
}
