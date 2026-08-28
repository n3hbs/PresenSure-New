<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgramResource;
use App\Services\ProgramService;

class ProgramController extends Controller
{
    public function __construct(
        protected ProgramService $programService
    ) {}

    public function index()
    {
        $programs = $this->programService->getPrograms();
        return $this->successResponse(
            ProgramResource::collection($programs),
            'Programs retrieved successfully.',
            200
        );
    }
}
