<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepository $departmentRepository
    ) {}

    public function getDepartments()
    {
        $department = $this->departmentRepository->getAll();
        if ($department->isEmpty()) {
            throw ValidationException::withMessages([
                'department_id' => [
                    'No department found.',
                ],
            ]);
        }

        return $department;
    }
}
