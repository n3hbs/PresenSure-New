<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Period;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SemesterRepository implements SemesterRepositoryInterface
{
    /**
     * Get all semesters eager loading schoolYear and periods.
     */
    public function getAllSemesters(): Collection
    {
        return Semester::with(['schoolYear', 'periods'])
            ->orderBy('semester_start', 'desc')
            ->orderBy('semester_id', 'desc')
            ->get();
    }

    /**
     * Find a semester by its ID.
     */
    public function findById(int $id): ?Semester
    {
        return Semester::with(['schoolYear', 'periods'])->find($id);
    }

    /**
     * Create a new semester record with optional periods.
     */
    public function createSemester(array $data): Semester
    {
        $periods = $data['periods'] ?? [];
        unset($data['periods']);

        if (! empty($data['is_active'])) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        $semester = Semester::create($data);

        if (! empty($periods)) {
            foreach ($periods as $period) {
                Period::create([
                    'semester_id' => $semester->semester_id,
                    'name' => $period['name'],
                    'period_start' => $period['period_start'],
                    'period_end' => $period['period_end'],
                    'description' => $period['description'] ?? null,
                ]);
            }
        }

        return $semester->load(['schoolYear', 'periods']);
    }

    /**
     * Update an existing semester record.
     */
    public function updateSemester(int $id, array $data): Semester
    {
        $semester = Semester::findOrFail($id);

        $periodsData = $data['periods'] ?? null;
        unset($data['periods']);

        if (! empty($data['is_active'])) {
            Semester::where('semester_id', '!=', $id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $semester->update($data);

        if (is_array($periodsData)) {
            $existingPeriods = Period::where('semester_id', $semester->semester_id)->get()->keyBy('name');
            $newNames = [];

            foreach ($periodsData as $p) {
                $name = $p['name'];
                $newNames[] = $name;

                if (isset($existingPeriods[$name])) {
                    $existingPeriods[$name]->update([
                        'period_start' => $p['period_start'],
                        'period_end' => $p['period_end'],
                        'description' => $p['description'] ?? null,
                    ]);
                } else {
                    Period::create([
                        'semester_id' => $semester->semester_id,
                        'name' => $name,
                        'period_start' => $p['period_start'],
                        'period_end' => $p['period_end'],
                        'description' => $p['description'] ?? null,
                    ]);
                }
            }

            foreach ($existingPeriods as $name => $existingPeriod) {
                if (! in_array($name, $newNames, true)) {
                    if ($existingPeriod->attendanceSessions()->exists()) {
                        throw ValidationException::withMessages([
                            'periods' => ["Cannot remove period '{$name}' because attendance sessions have already been recorded for it."],
                        ]);
                    }
                    $existingPeriod->delete();
                }
            }
        }

        return $semester->fresh(['schoolYear', 'periods']);
    }

    /**
     * Delete an existing semester record with dependency protection.
     *
     * @throws ValidationException
     */
    public function deleteSemester(int $id): bool
    {
        $semester = Semester::findOrFail($id);

        $dependencies = [];
        if ($semester->students()->exists()) {
            $dependencies[] = 'enrolled students';
        }
        if ($semester->courseBlocks()->exists()) {
            $dependencies[] = 'assigned course blocks';
        }
        if ($semester->schedules()->exists()) {
            $dependencies[] = 'class schedules';
        }
        if (Period::where('semester_id', $semester->semester_id)->whereHas('attendanceSessions')->exists()) {
            $dependencies[] = 'attendance sessions';
        }

        if (! empty($dependencies)) {
            throw ValidationException::withMessages([
                'semester' => ['Cannot delete this semester because it has existing dependent data: '.implode(', ', $dependencies).'.'],
            ]);
        }

        return (bool) $semester->delete();
    }

    /**
     * Mark a specific semester as active and deactivate all others.
     */
    public function setActiveSemester(int $id): Semester
    {
        Semester::where('semester_id', '!=', $id)->update(['is_active' => false]);

        $semester = Semester::findOrFail($id);
        $semester->update([
            'is_active' => true,
            'status' => 'Active',
        ]);

        return $semester->fresh(['schoolYear', 'periods']);
    }

    /**
     * Get currently active semester.
     * Primary: Date window aligned with today.
     * Fallback: Explicit is_active flag.
     * Fallback: Most recent semester.
     */
    public function getActiveSemester(): ?Semester
    {
        $today = now()->toDateString();

        // 1. Primary: Align on the current date (start <= today <= end)
        $semester = Semester::with(['schoolYear', 'periods'])
            ->whereDate('semester_start', '<=', $today)
            ->whereDate('semester_end', '>=', $today)
            ->first();

        if ($semester) {
            return $semester;
        }

        // 2. Fallback: Explicitly active semester
        $semester = Semester::with(['schoolYear', 'periods'])
            ->where('is_active', true)
            ->first();

        if ($semester) {
            return $semester;
        }

        // 3. Fallback: Most recent semester
        return Semester::with(['schoolYear', 'periods'])
            ->orderBy('semester_end', 'desc')
            ->first();
    }

    /**
     * Get all school years.
     */
    public function getAllSchoolYears(): Collection
    {
        return SchoolYear::orderBy('school_year_start', 'desc')->get();
    }

    /**
     * Create a school year.
     */
    public function createSchoolYear(array $data): SchoolYear
    {
        return SchoolYear::create($data);
    }

    /**
     * Get all archived (soft-deleted) semesters.
     *
     * @return Collection<int, Semester>
     */
    public function getArchivedSemesters(): Collection
    {
        return Semester::onlyTrashed()
            ->with(['schoolYear', 'periods'])
            ->orderByDesc('deleted_at')
            ->get();
    }

    /**
     * Restore an archived (soft-deleted) semester.
     */
    public function restoreSemester(int $id): Semester
    {
        $semester = Semester::onlyTrashed()->findOrFail($id);
        $semester->restore();

        return $semester->fresh(['schoolYear', 'periods']);
    }
}
