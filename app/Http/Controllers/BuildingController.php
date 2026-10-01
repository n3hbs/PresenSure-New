<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Building\CreateBuildingRequest;
use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Services\BuildingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function __construct(
        protected BuildingService $buildingService,
    ) {}

    /**
     * Display a listing of active buildings.
     */
    public function index(): JsonResponse
    {
        $buildings = $this->buildingService->getAllBuildings();

        return $this->successResponse(
            BuildingResource::collection($buildings),
            'Buildings retrieved successfully.',
            200
        );
    }

    /**
     * Store a newly created building.
     */
    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $building = $this->buildingService->createBuilding($request->validated());

        return $this->successResponse(
            new BuildingResource($building),
            'Building created successfully.',
            201
        );
    }

    /**
     * Display the specified building with its rooms and assigned class schedules.
     */
    public function show(Request $request, int $building_id): JsonResponse
    {
        $semesterId = $request->query('semester_id') !== null ? (int) $request->query('semester_id') : null;
        $building = $this->buildingService->getBuildingById($building_id, true, $semesterId);

        if (! $building) {
            return $this->errorResponse('Building not found.', 404);
        }

        return $this->successResponse(
            new BuildingResource($building),
            'Building retrieved successfully.',
            200
        );
    }

    /**
     * Update the specified building.
     */
    public function update(UpdateBuildingRequest $request, int $building_id): JsonResponse
    {
        $building = $this->buildingService->updateBuilding($building_id, $request->validated());

        return $this->successResponse(
            new BuildingResource($building),
            'Building updated successfully.',
            200
        );
    }

    /**
     * Archive (soft-delete) the specified building.
     */
    public function destroy(int $building_id): JsonResponse
    {
        $this->buildingService->deleteBuilding($building_id);

        return $this->successResponse(
            null,
            'Building archived successfully.',
            200
        );
    }

    /**
     * Display a listing of archived buildings.
     */
    public function archives(): JsonResponse
    {
        $archived = $this->buildingService->getArchivedBuildings();

        return $this->successResponse(
            BuildingResource::collection($archived),
            'Archived buildings retrieved successfully.',
            200
        );
    }

    /**
     * Restore an archived building.
     */
    public function restore(int $building_id): JsonResponse
    {
        $restored = $this->buildingService->restoreBuilding($building_id);

        return $this->successResponse(
            new BuildingResource($restored),
            'Building restored successfully.',
            200
        );
    }

    /**
     * Legacy building creation endpoint.
     */
    public function create(CreateBuildingRequest $request): JsonResponse
    {
        $building = $this->buildingService->create($request->validated());

        return $this->successResponse(
            new BuildingResource($building),
            'Building Successfully Created',
            201
        );
    }
}
