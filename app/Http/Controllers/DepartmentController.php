<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    /**
     * Display a listing of active departments.
     */
    public function index(): JsonResponse
    {
        $departments = $this->departmentService->getAllDepartments();

        return $this->successResponse(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully.',
            200
        );
    }

    /**
     * Store a newly created department.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        return $this->successResponse(
            new DepartmentResource($department),
            'Department created successfully.',
            201
        );
    }

    /**
     * Display the specified department.
     */
    public function show(int $department_id): JsonResponse
    {
        $department = $this->departmentService->getDepartmentById($department_id);

        if (! $department) {
            return $this->errorResponse('Department not found.', 404);
        }

        return $this->successResponse(
            new DepartmentResource($department),
            'Department retrieved successfully.',
            200
        );
    }

    /**
     * Update the specified department.
     */
    public function update(UpdateDepartmentRequest $request, int $department_id): JsonResponse
    {
        $department = $this->departmentService->updateDepartment($department_id, $request->validated());

        return $this->successResponse(
            new DepartmentResource($department),
            'Department updated successfully.',
            200
        );
    }

    /**
     * Archive (soft-delete) the specified department.
     */
    public function destroy(int $department_id): JsonResponse
    {
        $this->departmentService->deleteDepartment($department_id);

        return $this->successResponse(
            null,
            'Department archived successfully.',
            200
        );
    }

    /**
     * Display a listing of archived departments.
     */
    public function archives(): JsonResponse
    {
        $archived = $this->departmentService->getArchivedDepartments();

        return $this->successResponse(
            DepartmentResource::collection($archived),
            'Archived departments retrieved successfully.',
            200
        );
    }

    /**
     * Restore an archived department.
     */
    public function restore(int $department_id): JsonResponse
    {
        $restored = $this->departmentService->restoreDepartment($department_id);

        return $this->successResponse(
            new DepartmentResource($restored),
            'Department restored successfully.',
            200
        );
    }
}
