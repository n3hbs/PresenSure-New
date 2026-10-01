<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Building;
use App\Repositories\Interfaces\BuildingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuildingService
{
    public function __construct(
        protected BuildingRepositoryInterface $buildingRepository
    ) {}

    /**
     * Get all active buildings with rooms count.
     *
     * @return Collection<int, Building>
     */
    public function getAllBuildings(): Collection
    {
        return $this->buildingRepository->getAll();
    }

    /**
     * Get all archived (soft-deleted) buildings.
     *
     * @return Collection<int, Building>
     */
    public function getArchivedBuildings(): Collection
    {
        return $this->buildingRepository->getArchived();
    }

    /**
     * Get a building by ID, optionally eager-loading rooms and their assigned schedules.
     */
    public function getBuildingById(int $id, bool $withRoomsAndSchedules = true, ?int $semesterId = null): ?Building
    {
        return $this->buildingRepository->findById($id, $withRoomsAndSchedules, $semesterId);
    }

    /**
     * Create a new building inside a transaction.
     */
    public function createBuilding(array $data): Building
    {
        return DB::transaction(function () use ($data) {
            return $this->buildingRepository->create($data);
        });
    }

    /**
     * Update an existing building inside a transaction.
     */
    public function updateBuilding(int $id, array $data): Building
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->buildingRepository->update($id, $data);
        });
    }

    /**
     * Soft-delete a building after checking dependencies.
     */
    public function deleteBuilding(int $id): bool
    {
        $dependencies = $this->buildingRepository->hasDependencies($id);

        if (! empty($dependencies)) {
            throw ValidationException::withMessages([
                'building' => [
                    'Cannot archive this building: '.implode(' ', $dependencies),
                ],
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->buildingRepository->delete($id);
        });
    }

    /**
     * Restore an archived building inside a transaction.
     */
    public function restoreBuilding(int $id): Building
    {
        return DB::transaction(function () use ($id) {
            return $this->buildingRepository->restore($id);
        });
    }

    /**
     * Legacy building creation method.
     */
    public function create(array $data): Building
    {
        return $this->createBuilding($data);
    }
}
