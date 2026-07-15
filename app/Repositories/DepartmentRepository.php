<?php

namespace App\Repositories;

use App\Models\Department;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function getAll()
    {
        return Department::orderBy('department_name')->get();
    }
}
