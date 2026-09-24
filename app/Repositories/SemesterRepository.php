<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Semester;
use App\Repositories\Interfaces\SemesterRepositoryInterface;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function getActiveSemester()
    {
        $semester = Semester::with('schoolYear')
            ->whereDate('semester_start', '<=', now()->toDateString())
            ->whereDate('semester_end', '>=', now()->toDateString())
            ->first();

        if (! $semester) {
            $semester = Semester::with('schoolYear')
                ->orderBy('semester_end', 'desc')
                ->first();
        }

        return $semester;
    }
}
