<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $departmentRepository
    ) {}

    /**
     * Legacy getter preserved for backward compatibility.
     *
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departmentRepository->getAll();
    }

    /**
     * Get all active departments with programs and instructors count.
     *
     * @return Collection<int, Department>
     */
    public function getAllDepartments(): Collection
    {
        return $this->departmentRepository->getAll();
    }

    /**
     * Get all archived departments.
     *
     * @return Collection<int, Department>
     */
    public function getArchivedDepartments(): Collection
    {
        return $this->departmentRepository->getArchived();
    }

    /**
     * Get department by ID with loaded relations.
     */
    public function getDepartmentById(int $id): ?Department
    {
        return $this->departmentRepository->findById($id);
    }

    /**
     * Create department with optional initial programs inside a transaction.
     */
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            return $this->departmentRepository->create($data);
        });
    }

    /**
     * Update department and its programs inside a transaction.
     */
    public function updateDepartment(int $id, array $data): Department
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->departmentRepository->update($id, $data);
        });
    }

    /**
     * Soft delete department with dependency validation.
     */
    public function deleteDepartment(int $id): bool
    {
        $dependencies = $this->departmentRepository->hasDependencies($id);

        if (! empty($dependencies)) {
            throw ValidationException::withMessages([
                'department' => [
                    'Cannot archive this department: '.implode(' ', $dependencies),
                ],
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->departmentRepository->delete($id);
        });
    }

    /**
     * Restore an archived department inside a transaction.
     */
    public function restoreDepartment(int $id): Department
    {
        return DB::transaction(function () use ($id) {
            return $this->departmentRepository->restore($id);
        });
    }
}
