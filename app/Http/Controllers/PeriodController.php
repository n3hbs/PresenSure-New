<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Period\CreatePeriodRequest;
use App\Http\Resources\PeriodResource;
use App\Services\PeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periodService
    ) {}

    public function create(CreatePeriodRequest $request): JsonResponse
    {
        $period = $this->periodService->create($request->validated());

        return $this->successResponse(
            new PeriodResource($period),
            'Period Successfully Created',
            201
        );
    }

    public function getActivePeriod(): JsonResponse
    {
        $period = $this->periodService->getActivePeriod();

        return $this->successResponse(
            new PeriodResource($period),
            'Active Period Retrieved Successfully.',
            200
        );
    }

    public function destroy(int $period_id): Response
    {
        $this->periodService->deletePeriod($period_id);

        return response()->noContent();
    }
}
