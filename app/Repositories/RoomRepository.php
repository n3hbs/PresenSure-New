<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Room;
use App\Models\Semester;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository implements RoomRepositoryInterface
{
    /**
     * Get all active rooms, optionally filtered by building_id, floor_no, or status.
     *
     * @return Collection<int, Room>
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Room::with('building')->withCount('schedules');

        if (! empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        if (isset($filters['floor_no']) && $filters['floor_no'] !== null && $filters['floor_no'] !== '') {
            $query->where('floor_no', (int) $filters['floor_no']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('floor_no')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all archived (soft-deleted) rooms.
     *
     * @return Collection<int, Room>
     */
    public function getArchived(): Collection
    {
        return Room::onlyTrashed()
            ->with('building')
            ->withCount('schedules')
            ->orderByDesc('deleted_at')
            ->get();
    }

    /**
     * Find a room by ID, optionally eager-loading assigned schedules.
     */
    public function findById(int $id, bool $withSchedules = false, ?int $semesterId = null): ?Room
    {
        $query = Room::with('building')
            ->withCount('schedules')
            ->where('room_id', $id);

        if ($withSchedules) {
            $semester = $semesterId
                ? Semester::with('schoolYear')->find($semesterId)
                : Semester::with('schoolYear')->where('is_active', true)->first();

            $query->with(['schedules' => function ($q) use ($semester) {
                if ($semester) {
                    $q->where('semester_id', $semester->semester_id);
                }
                $q->with([
                    'scheduleDays',
                    'courseBlock.course',
                    'semester.schoolYear',
                ])->orderBy('start_time');
            }]);
        }

        return $query->first();
    }

    /**
     * Create a new room.
     */
    public function create(array $data): Room
    {
        $room = Room::create($data);

        return $this->findById((int) $room->room_id) ?? $room;
    }

    /**
     * Update an existing room.
     */
    public function update(int $id, array $data): Room
    {
        $room = Room::findOrFail($id);
        $room->update($data);

        return $this->findById((int) $room->room_id) ?? $room;
    }

    /**
     * Soft-delete a room.
     */
    public function delete(int $id): bool
    {
        $room = Room::findOrFail($id);

        return (bool) $room->delete();
    }

    /**
     * Restore an archived room.
     */
    public function restore(int $id): Room
    {
        $room = Room::onlyTrashed()->findOrFail($id);
        $room->restore();

        return $this->findById((int) $room->room_id) ?? $room;
    }

    /**
     * Check if room has dependencies that prevent deletion.
     *
     * @return array<string>
     */
    public function hasDependencies(int $id): array
    {
        $room = Room::find($id);
        if (! $room) {
            return [];
        }

        $reasons = [];
        $scheduleCount = $room->schedules()->count();
        if ($scheduleCount > 0) {
            $reasons[] = "Room has {$scheduleCount} assigned class schedule(s).";
        }

        $bleCount = $room->bleDevices()->count();
        if ($bleCount > 0) {
            $reasons[] = "Room has {$bleCount} associated BLE beacon device(s).";
        }

        return $reasons;
    }
}
