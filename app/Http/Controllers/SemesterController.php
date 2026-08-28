<?php

namespace App\Http\Controllers;

use App\Http\Resources\SemesterResource;
use App\Services\SemesterService;

class SemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService,
    ) {}

    public function getActiveSemester()
    {
        $semester = $this->semesterService->getActiveSemester();


        return $this->successResponse(
            new SemesterResource($semester),
            'Active semester retrieved successfully.',
            200
        );
    }
}
