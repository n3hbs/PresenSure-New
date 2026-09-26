<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Semester;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SemesterService
{
    public function __construct(
        protected SemesterRepositoryInterface $semesterRepository,
    ) {}

    /**
     * Get all semesters ordered by start date desc.
     *
     * @return Collection<int, Semester>
     */
    public function getAllSemesters(): Collection
    {
        return $this->semesterRepository->getAllSemesters();
    }

    /**
     * Find a semester by ID.
     */
    public function getSemesterById(int $id): ?Semester
    {
        return $this->semesterRepository->findById($id);
    }

    /**
     * Get the active semester.
     *
     * @throws ValidationException
     */
    public function getActiveSemester(): Semester
    {
        $semester = $this->semesterRepository->getActiveSemester();

        if (! $semester) {
            throw ValidationException::withMessages([
                'semester_id' => [
                    'No active semester found.',
                ],
            ]);
        }

        return $semester;
    }

    /**
     * Create a new semester.
     */
    public function createSemester(array $data): Semester
    {
        return DB::transaction(function () use ($data) {
            return $this->semesterRepository->createSemester($data);
        });
    }

    /**
     * Update an existing semester.
     */
    public function updateSemester(int $id, array $data): Semester
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->semesterRepository->updateSemester($id, $data);
        });
    }

    /**
     * Delete / Archive a semester.
     */
    public function deleteSemester(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->semesterRepository->deleteSemester($id);
        });
    }

    /**
     * Set a semester as active.
     */
    public function setActiveSemester(int $id): Semester
    {
        return DB::transaction(function () use ($id) {
            return $this->semesterRepository->setActiveSemester($id);
        });
    }

    /**
     * Get all school years.
     */
    public function getAllSchoolYears(): Collection
    {
        return $this->semesterRepository->getAllSchoolYears();
    }

    /**
     * Create a school year.
     */
    public function createSchoolYear(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->semesterRepository->createSchoolYear($data);
        });
    }

    /**
     * Get all archived (soft-deleted) semesters.
     *
     * @return Collection<int, Semester>
     */
    public function getArchivedSemesters(): Collection
    {
        return $this->semesterRepository->getArchivedSemesters();
    }

    /**
     * Restore an archived semester.
     */
    public function restoreSemester(int $id): Semester
    {
        return DB::transaction(function () use ($id) {
            return $this->semesterRepository->restoreSemester($id);
        });
    }
}
