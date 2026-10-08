<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BleDevice;
use App\Models\Building;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Semester;
use App\Repositories\Interfaces\BuildingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BuildingRepository implements BuildingRepositoryInterface
{
    /**
     * Get all active buildings with rooms count.
     *
     * @return Collection<int, Building>
     */
    public function getAll(): Collection
    {
        return Building::withCount('rooms')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all archived (soft-deleted) buildings.
     *
     * @return Collection<int, Building>
     */
    public function getArchived(): Collection
    {
        return Building::onlyTrashed()
            ->withCount('rooms')
            ->orderByDesc('deleted_at')
            ->get();
    }

    /**
     * Find a building by ID, optionally eager-loading rooms and their assigned schedules.
     */
    public function findById(int $id, bool $withRoomsAndSchedules = false, ?int $semesterId = null): ?Building
    {
        $building = Building::withCount('rooms')
            ->where('building_id', $id)
            ->first();

        if (! $building) {
            return null;
        }

        if ($withRoomsAndSchedules) {
            $semester = $semesterId
                ? Semester::with('schoolYear')->find($semesterId)
                : Semester::with('schoolYear')->where('is_active', true)->first();

            $building->active_semester = $semester;

            $rooms = Room::where('building_id', $building->building_id)
                ->withCount('schedules')
                ->with(['schedules' => function ($query) use ($semester) {
                    if ($semester) {
                        $query->where('semester_id', $semester->semester_id);
                    }
                    $query->with([
                        'scheduleDays',
                        'courseBlock.course',
                        'courseBlock.instructor',
                        'courseBlock.userCourseBlocks.user',
                        'semester.schoolYear',
                    ])->orderBy('start_time');
                }])
                ->orderBy('floor_no')
                ->orderBy('name')
                ->get();

            $building->setRelation('rooms', $rooms);
        }

        return $building;
    }

    /**
     * Create a new building (with optional initial rooms).
     */
    public function create(array $data): Building
    {
        $roomsData = $data['rooms'] ?? null;
        unset($data['rooms']);

        $building = Building::create($data);

        if (! empty($roomsData) && is_array($roomsData)) {
            foreach ($roomsData as $r) {
                if (! empty($r['name']) && isset($r['floor_no'])) {
                    $building->rooms()->create([
                        'name' => trim((string) $r['name']),
                        'floor_no' => (int) $r['floor_no'],
                        'capacity' => isset($r['capacity']) ? (int) $r['capacity'] : null,
                        'status' => $r['status'] ?? 'Active',
                    ]);
                }
            }
        }

        return $this->findById((int) $building->building_id, true) ?? $building;
    }

    /**
     * Update an existing building.
     */
    public function update(int $id, array $data): Building
    {
        $building = Building::findOrFail($id);
        $building->update($data);

        return $this->findById((int) $building->building_id) ?? $building;
    }

    /**
     * Soft-delete a building and its rooms.
     */
    public function delete(int $id): bool
    {
        $building = Building::findOrFail($id);
        $building->rooms()->delete();

        return (bool) $building->delete();
    }

    /**
     * Restore an archived building and its rooms.
     */
    public function restore(int $id): Building
    {
        $building = Building::onlyTrashed()->findOrFail($id);
        $building->restore();

        Room::onlyTrashed()->where('building_id', $building->building_id)->restore();

        return $this->findById((int) $building->building_id, true) ?? $building;
    }

    /**
     * Check if building has dependencies that prevent deletion.
     *
     * @return array<string>
     */
    public function hasDependencies(int $id): array
    {
        $building = Building::find($id);
        if (! $building) {
            return [];
        }

        $reasons = [];
        $roomIds = $building->rooms()->pluck('room_id');

        if ($roomIds->isNotEmpty()) {
            $scheduleCount = Schedule::whereIn('room_id', $roomIds)->count();
            if ($scheduleCount > 0) {
                $reasons[] = "Building has {$scheduleCount} class schedule(s) assigned to its rooms.";
            }

            $deviceCount = BleDevice::whereIn('room_id', $roomIds)->count();
            if ($deviceCount > 0) {
                $reasons[] = "Building has {$deviceCount} associated BLE beacon device(s).";
            }
        }

        return $reasons;
    }
}
