<?php

namespace App\Http\Controllers;

use App\Http\Requests\Period\CreatePeriodRequest;
use App\Services\PeriodService;
use App\Http\Resources\PeriodResource;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periodService
    ) {}

    public function create(CreatePeriodRequest $request)
    {
        $period = $this->periodService->create($request->validated());
        return $this->successResponse(
            new PeriodResource($period),
            'Period Successfully Created',
            201
        );
    }

    public function getActivePeriod()
    {
        $period = $this->periodService->getActivePeriod();
        return $this->successResponse(
            new PeriodResource($period),
            'Active Period Retrieved Successfully.',
            200
        );
    }
}
