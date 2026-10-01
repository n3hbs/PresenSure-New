<?php

declare(strict_types=1);

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
            'students_count' => (int) ($this->students_count ?? ($this->relationLoaded('userCourseBlocks') ? $this->userCourseBlocks->count() : 0)),
            'schedules_count' => (int) ($this->schedules_count ?? ($this->relationLoaded('schedules') ? $this->schedules->count() : 0)),
            'course' => new CourseResource(
                $this->whenLoaded('course')
            ),
            'semester' => new SemesterResource(
                $this->whenLoaded('semester')
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
