<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'department_id' => $this->department_id,
            'department_code' => $this->department_code,
            'department_name' => $this->department_name,
            'description' => $this->description,
            'programs_count' => $this->programs_count ?? ($this->relationLoaded('programs') ? $this->programs->count() : 0),
            'instructors_count' => $this->instructors_count ?? ($this->relationLoaded('instructors') ? $this->instructors->count() : 0),
            'students_count' => $this->students_count ?? 0,
            'programs' => ProgramResource::collection($this->whenLoaded('programs')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
