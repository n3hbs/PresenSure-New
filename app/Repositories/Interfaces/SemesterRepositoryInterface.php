<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Collection;

interface SemesterRepositoryInterface
{
    /**
     * Get all semesters eager loading schoolYear.
     */
    public function getAllSemesters(): Collection;

    /**
     * Find a semester by its ID.
     */
    public function findById(int $id): ?Semester;

    /**
     * Create a new semester record.
     */
    public function createSemester(array $data): Semester;

    /**
     * Update an existing semester record.
     */
    public function updateSemester(int $id, array $data): Semester;

    /**
     * Delete an existing semester record.
     */
    public function deleteSemester(int $id): bool;

    /**
     * Mark a specific semester as active and deactivate all others.
     */
    public function setActiveSemester(int $id): Semester;

    /**
     * Get currently active semester.
     */
    public function getActiveSemester(): ?Semester;

    /**
     * Get all school years.
     */
    public function getAllSchoolYears(): Collection;

    /**
     * Create a school year.
     */
    public function createSchoolYear(array $data);

    /**
     * Get all archived (soft-deleted) semesters.
     *
     * @return Collection<int, Semester>
     */
    public function getArchivedSemesters(): Collection;

    /**
     * Restore an archived (soft-deleted) semester.
     */
    public function restoreSemester(int $id): Semester;
}
