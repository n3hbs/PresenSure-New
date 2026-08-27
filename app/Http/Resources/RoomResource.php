<?php

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
            'name' => $this->name,
            'floor_no' => $this->floor_no,
            'building' => new BuildingResource(
                $this->whenLoaded('building')
            ),
        ];
    }
}
