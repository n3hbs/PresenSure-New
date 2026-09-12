<?php

namespace App\Http\Controllers;

use App\Http\Requests\BleDetection\StoreBleDetectionRequest;
use App\Http\Resources\BleDetectionResource;
use App\Services\BleDetectionService;
use Illuminate\Http\JsonResponse;

class BleDetectionController extends Controller
{
    public function __construct(
        protected BleDetectionService $bleDetectionService
    ) {}

    public function store(StoreBleDetectionRequest $request): JsonResponse
    {
        $bleDetection = $this->bleDetectionService->createBleDetection(
            $request->validated(),
            $request->user()
        );

        return $this->successResponse(
            new BleDetectionResource($bleDetection),
            'BLE detection recorded successfully.',
            201
        );
    }
}
