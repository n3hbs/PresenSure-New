<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface
{
    /**
     * Get all active rooms, optionally filtered by building_id, floor_no, or status.
     *
     * @return Collection<int, Room>
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get all archived rooms.
     *
     * @return Collection<int, Room>
     */
    public function getArchived(): Collection;

    /**
     * Find a room by ID, optionally eager-loading assigned schedules.
     */
    public function findById(int $id, bool $withSchedules = false, ?int $semesterId = null): ?Room;

    /**
     * Create a new room.
     */
    public function create(array $data): Room;

    /**
     * Update an existing room.
     */
    public function update(int $id, array $data): Room;

    /**
     * Soft-delete a room.
     */
    public function delete(int $id): bool;

    /**
     * Restore an archived room.
     */
    public function restore(int $id): Room;

    /**
     * Check if room has dependencies that prevent deletion.
     *
     * @return array<string>
     */
    public function hasDependencies(int $id): array;
}
