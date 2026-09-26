<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    /**
     * Get all active departments with counts.
     *
     * @return Collection<int, Department>
     */
    public function getAll(): Collection
    {
        return Department::withCount(['programs', 'instructors'])
            ->with('programs')
            ->orderBy('department_name')
            ->get();
    }

    /**
     * Get all archived (soft-deleted) departments.
     *
     * @return Collection<int, Department>
     */
    public function getArchived(): Collection
    {
        return Department::onlyTrashed()
            ->withCount(['programs', 'instructors'])
            ->with('programs')
            ->orderByDesc('deleted_at')
            ->get();
    }

    /**
     * Find department by ID with loaded relations.
     */
    public function findById(int $id, bool $withRelations = true): ?Department
    {
        $query = Department::where('department_id', $id);

        if ($withRelations) {
            $query->withCount(['programs', 'instructors'])
                ->with(['programs' => function ($q) {
                    $q->withCount('student');
                }]);
        }

        return $query->first();
    }

    /**
     * Create a new department with optional initial programs.
     */
    public function create(array $data): Department
    {
        $programsData = $data['programs'] ?? [];
        unset($data['programs']);

        $department = Department::create($data);

        if (! empty($programsData) && is_array($programsData)) {
            foreach ($programsData as $prog) {
                if (! empty($prog['program_code']) && ! empty($prog['program_name'])) {
                    $department->programs()->create([
                        'program_code' => strtoupper(trim((string) $prog['program_code'])),
                        'program_name' => trim((string) $prog['program_name']),
                        'program_years' => ! empty($prog['program_years']) ? (int) $prog['program_years'] : 4,
                    ]);
                }
            }
        }

        return $department->fresh(['programs']);
    }

    /**
     * Update an existing department and its programs.
     */
    public function update(int $id, array $data): Department
    {
        $department = Department::findOrFail($id);

        $programsData = $data['programs'] ?? null;
        unset($data['programs']);

        $department->update($data);

        if ($programsData !== null && is_array($programsData)) {
            $submittedIds = [];

            foreach ($programsData as $prog) {
                if (empty($prog['program_code']) || empty($prog['program_name'])) {
                    continue;
                }

                $progId = ! empty($prog['program_id']) ? (int) $prog['program_id'] : null;

                if ($progId) {
                    $existingProg = Program::where('department_id', $department->department_id)
                        ->where('program_id', $progId)
                        ->first();

                    if ($existingProg) {
                        $existingProg->update([
                            'program_code' => strtoupper(trim((string) $prog['program_code'])),
                            'program_name' => trim((string) $prog['program_name']),
                            'program_years' => ! empty($prog['program_years']) ? (int) $prog['program_years'] : 4,
                        ]);
                        $submittedIds[] = $existingProg->program_id;
                    }
                } else {
                    $newProg = $department->programs()->create([
                        'program_code' => strtoupper(trim((string) $prog['program_code'])),
                        'program_name' => trim((string) $prog['program_name']),
                        'program_years' => ! empty($prog['program_years']) ? (int) $prog['program_years'] : 4,
                    ]);
                    $submittedIds[] = $newProg->program_id;
                }
            }

            // Remove programs that were not included in the submission (if they have no dependent students)
            $programsToDelete = Program::where('department_id', $department->department_id)
                ->whereNotIn('program_id', $submittedIds)
                ->get();

            foreach ($programsToDelete as $delProg) {
                if ($delProg->student()->count() === 0) {
                    $delProg->delete();
                }
            }
        }

        return $department->fresh(['programs']);
    }

    /**
     * Soft-delete a department.
     */
    public function delete(int $id): bool
    {
        $department = Department::findOrFail($id);

        return (bool) $department->delete();
    }

    /**
     * Restore an archived department.
     */
    public function restore(int $id): Department
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();

        return $department->fresh(['programs']);
    }

    /**
     * Check if a department has dependencies that prevent deletion.
     */
    public function hasDependencies(int $id): array
    {
        $department = Department::find($id);
        if (! $department) {
            return [];
        }

        $reasons = [];

        $instructorCount = $department->instructors()->count();
        if ($instructorCount > 0) {
            $reasons[] = "Department has {$instructorCount} assigned instructor(s).";
        }

        $programIds = $department->programs()->pluck('program_id');
        if ($programIds->isNotEmpty()) {
            $studentCount = Student::whereIn('program_id', $programIds)->count();
            if ($studentCount > 0) {
                $reasons[] = "Department programs have {$studentCount} enrolled student(s).";
            }
        }

        return $reasons;
    }
}
