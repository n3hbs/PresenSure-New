<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepository $departmentRepository
    ) {}

    public function getDepartments()
    {
        return $this->departmentRepository->getAll();
    }
}
