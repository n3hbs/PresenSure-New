<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

interface BuildingRepositoryInterface
{
    /**
     * Get all active buildings.
     *
     * @return Collection<int, Building>
     */
    public function getAll(): Collection;

    /**
     * Get all archived buildings.
     *
     * @return Collection<int, Building>
     */
    public function getArchived(): Collection;

    /**
     * Find a building by ID, optionally eager-loading rooms and their assigned schedules.
     */
    public function findById(int $id, bool $withRoomsAndSchedules = false, ?int $semesterId = null): ?Building;

    /**
     * Create a new building (with optional initial rooms).
     */
    public function create(array $data): Building;

    /**
     * Update an existing building.
     */
    public function update(int $id, array $data): Building;

    /**
     * Soft-delete a building.
     */
    public function delete(int $id): bool;

    /**
     * Restore an archived building.
     */
    public function restore(int $id): Building;

    /**
     * Check if building has dependencies that prevent deletion.
     *
     * @return array<string>
     */
    public function hasDependencies(int $id): array;
}
