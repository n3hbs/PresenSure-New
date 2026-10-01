<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeSemester = $this->active_semester;

        return [
            'course_id' => $this->course_id,
            'subject_code' => $this->subject_code,
            'name' => $this->name,
            'description' => $this->description,
            'course_blocks_count' => $this->course_blocks_count ?? ($this->relationLoaded('courseBlocks') ? $this->courseBlocks->count() : 0),
            'active_semester_blocks_count' => (int) ($this->active_semester_blocks_count ?? ($this->relationLoaded('courseBlocks') ? $this->courseBlocks->count() : 0)),
            'active_semester' => $activeSemester ? [
                'semester_id' => $activeSemester->semester_id,
                'term' => $activeSemester->term,
                'school_year' => $activeSemester->schoolYear ? $activeSemester->schoolYear->school_year_start.' - '.$activeSemester->schoolYear->school_year_end : null,
                'is_active' => (bool) $activeSemester->is_active,
            ] : null,
            'course_blocks' => CourseBlockResource::collection($this->whenLoaded('courseBlocks')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
