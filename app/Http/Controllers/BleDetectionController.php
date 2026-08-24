<?php

namespace App\Http\Controllers;

use App\Http\Requests\BleDetection\StoreBleDetectionRequest;
use App\Services\BleDetectionService;
use Illuminate\Http\Request;

class BleDetectionController extends Controller
{
    public function __construct(
        protected BleDetectionService $bleDetectionService
    ) {}

    public function store(StoreBleDetectionRequest $request)
    {
        $result = $this->bleDetectionService->createBleDetection($request->validated(), $request->user());

        return $this->successResponse(
            $result['data'],
            $result['message'],
            201
        );
    }
}
