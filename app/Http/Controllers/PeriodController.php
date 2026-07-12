<?php

namespace App\Http\Controllers;

use App\Http\Requests\Period\CreatePeriodRequest;
use App\Services\PeriodService;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periodService
    ) {}

    public function create(CreatePeriodRequest $request)
    {
        $this->periodService->createPeriod($request->validated());

        return response()->json([
            'message' => 'Period Successfully Created',
        ], 201);
    }
}
