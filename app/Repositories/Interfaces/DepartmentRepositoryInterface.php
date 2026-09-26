<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    /**
     * Get all active departments with counts.
     *
     * @return Collection<int, Department>
     */
    public function getAll(): Collection;

    /**
     * Get all archived (soft-deleted) departments.
     *
     * @return Collection<int, Department>
     */
    public function getArchived(): Collection;

    /**
     * Find department by ID with loaded relations.
     */
    public function findById(int $id, bool $withRelations = true): ?Department;

    /**
     * Create a new department.
     */
    public function create(array $data): Department;

    /**
     * Update an existing department.
     */
    public function update(int $id, array $data): Department;

    /**
     * Soft-delete a department.
     */
    public function delete(int $id): bool;

    /**
     * Restore an archived department.
     */
    public function restore(int $id): Department;

    /**
     * Check if a department has dependencies that prevent deletion.
     */
    public function hasDependencies(int $id): array;
}
