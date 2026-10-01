<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'room_id' => $this->room_id,
            'building_id' => $this->building_id,
            'name' => $this->name,
            'floor_no' => $this->floor_no,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'schedules_count' => (int) ($this->schedules_count ?? ($this->relationLoaded('schedules') ? $this->schedules->count() : 0)),
            'building' => new BuildingResource(
                $this->whenLoaded('building')
            ),
            'schedules' => ScheduleResource::collection(
                $this->whenLoaded('schedules')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
