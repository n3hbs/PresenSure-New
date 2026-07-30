<?php

namespace App\Http\Controllers;

use App\Http\Resources\SemesterResource;
use App\Services\SemesterService;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService,
    ) {}

    public function getActiveSemester()
    {
        $semester = $this->semesterService->getActiveSemester();


        return $this->successResponse(
            $semester['data'],
            $semester['message'],
            201
        );
    }
}
