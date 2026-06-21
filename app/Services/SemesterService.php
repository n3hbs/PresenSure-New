<?php

namespace App\Services;

use App\Repositories\SemesterRepository;

class SemesterService
{
    public function __construct(
        private SemesterRepository $semesterRepositry,
    ) {}

    public function getActiveSemester() {
        return $this->semesterRepositry->getActiveSemester();
    }
}
