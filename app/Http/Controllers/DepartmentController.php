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
        return DepartmentResource::collection(
            $this->departmentService->getDepartments()
        );
    }
}
