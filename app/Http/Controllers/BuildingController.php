<?php

namespace App\Http\Controllers;

use App\Http\Requests\Building\CreateBuildingRequest;
use App\Services\BuildingService;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function __construct(
        protected BuildingService $buildingService,
    ) {}

    public function create(CreateBuildingRequest $request) {
        $this->buildingService->createBuilding($request->validated());
        return response()->json([
            'message' => 'Building Successfully Created',
        ], 201);
    }
}
