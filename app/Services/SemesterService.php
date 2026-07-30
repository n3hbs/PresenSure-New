<?php

namespace App\Services;

use App\Repositories\SemesterRepository;

class SemesterService
{
    public function __construct(
        private SemesterRepository $semesterRepositry,
    ) {}

    public function getActiveSemester()
    {
        $semester = $this->semesterRepositry->getActiveSemester();

        if ($semester) {
            return [
                'data' => $semester,
                'message' => 'Active semester retrieved successfully',
            ];
        }

        return [
            'data' => null,
            'message' => 'No active semester found',
        ];
    }
}
