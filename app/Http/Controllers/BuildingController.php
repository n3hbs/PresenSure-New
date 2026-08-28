<?php

namespace App\Http\Controllers;

use App\Http\Requests\Building\CreateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(
        protected BuildingService $buildingService,
    ) {}

    public function create(CreateBuildingRequest $request)
    {
        $building = $this->buildingService->create($request->validated());
        return $this->successResponse(
            new BuildingResource($building),
            'Building Successfully Created',
            201
        );
    }
}
