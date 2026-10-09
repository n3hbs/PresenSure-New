<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Room;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function __construct(
        protected RoomRepositoryInterface $roomRepository
    ) {}

    /**
     * Get all active rooms, optionally filtered by building_id, floor_no, or status.
     *
     * @return Collection<int, Room>
     */
    public function getAllRooms(array $filters = []): Collection
    {
        return $this->roomRepository->getAll($filters);
    }

    /**
     * Get all archived (soft-deleted) rooms.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Room>
     */
    public function getArchivedRooms(array $filters = []): Collection
    {
        return $this->roomRepository->getArchived($filters);
    }

    /**
     * Get a room by ID, optionally eager-loading assigned schedules.
     */
    public function getRoomById(int $id, bool $withSchedules = true, ?int $semesterId = null): ?Room
    {
        return $this->roomRepository->findById($id, $withSchedules, $semesterId);
    }

    /**
     * Create a new room inside a transaction.
     */
    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            return $this->roomRepository->create($data);
        });
    }

    /**
     * Update an existing room inside a transaction.
     */
    public function updateRoom(int $id, array $data): Room
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->roomRepository->update($id, $data);
        });
    }

    /**
     * Soft-delete a room after checking dependencies.
     */
    public function deleteRoom(int $id): bool
    {
        $dependencies = $this->roomRepository->hasDependencies($id);

        if (! empty($dependencies)) {
            throw ValidationException::withMessages([
                'room' => [
                    'Cannot archive this room: '.implode(' ', $dependencies),
                ],
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->roomRepository->delete($id);
        });
    }

    /**
     * Restore an archived room inside a transaction.
     */
    public function restoreRoom(int $id): Room
    {
        $room = $this->roomRepository->findTrashedById($id);

        if ($room && $room->building && $room->building->trashed()) {
            throw ValidationException::withMessages([
                'room' => [
                    "Cannot restore room: Parent building ({$room->building->name}) is currently archived. Please restore the building first.",
                ],
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->roomRepository->restore($id);
        });
    }

    /**
     * Legacy room creation method.
     */
    public function create(array $data): Room
    {
        return $this->createRoom($data);
    }
}
