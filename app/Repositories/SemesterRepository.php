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
        return Semester::where(
            'semester_start',
            '<=',
            now()
        )
            ->where(
                'semester_end',
                '>=',
                now()
            )
            ->first();
    }
}
