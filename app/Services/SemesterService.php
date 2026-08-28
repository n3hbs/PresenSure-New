<?php

namespace App\Services;

use App\Repositories\SemesterRepository;
use Illuminate\Validation\ValidationException;

class SemesterService
{
    public function __construct(
        private SemesterRepository $semesterRepositry,
    ) {}

    public function getActiveSemester()
    {
        $semester = $this->semesterRepositry->getActiveSemester();

        if (! $semester) {
            throw ValidationException::withMessages([
                'semester_id' => [
                    'No active semester found.',
                ],
            ]);
        }

        return $semester;
    }
}
