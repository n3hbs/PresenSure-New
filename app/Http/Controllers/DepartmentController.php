<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    public function index()
    {
        $departments = $this->departmentService->getDepartments();
        return $this->successResponse(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully.',
            200
        );
    }
}
