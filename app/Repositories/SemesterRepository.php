<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Interfaces\SemesterRepositoryInterface;
use App\Models\Semester;
use Carbon\Carbon;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function getActiveSemester()
    {
        return Semester::whereDate('semester_start', '<=', now()->toDateString())
            ->whereDate('semester_end', '>=', now()->toDateString())
            ->first();
    }
}
