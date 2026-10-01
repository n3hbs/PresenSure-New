<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'building_id' => $this->building_id,
            'code' => $this->code,
            'name' => $this->name,
            'rooms_count' => (int) ($this->rooms_count ?? ($this->relationLoaded('rooms') ? $this->rooms->count() : 0)),
            'active_semester' => $this->when(isset($this->active_semester) && $this->active_semester !== null, function () {
                return new SemesterResource($this->active_semester);
            }),
            'rooms' => RoomResource::collection(
                $this->whenLoaded('rooms')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
