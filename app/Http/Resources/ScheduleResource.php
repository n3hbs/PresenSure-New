<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'schedule_id' => $this->schedule_id,
            'block_code' => $this->block_code,
            'schedule_type' => $this->schedule_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'days' => $this->relationLoaded('scheduleDays')
                ? $this->scheduleDays->pluck('day')->values()
                : [],
            'room' => new RoomResource(
                $this->whenLoaded('room')
            ),
        ];
    }
}
