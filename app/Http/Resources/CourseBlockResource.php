<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'course_block_id' => $this->course_block_id,
            'course_id' => $this->course_id,
            'semester_id' => $this->semester_id,
            'block_code' => $this->block_code,
            'course' => new CourseResource(
                $this->whenLoaded('course')
            ),
            'semester' => new SemesterResource(
                $this->whenLoaded('semester')
            ),
            'schedules' => ScheduleResource::collection(
                $this->whenLoaded('schedules')
            ),
        ];
    }
}
