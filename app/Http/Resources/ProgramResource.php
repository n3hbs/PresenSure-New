<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'program_id' => $this->program_id,
            'program_code' => $this->program_code,
            'program_name' => $this->program_name,
            'program_years' => $this->program_years,
            'students_count' => (int) (
                $this->students_count
                ?? $this->student_count
                ?? ($this->relationLoaded('students') ? $this->students->count() : null)
                ?? ($this->relationLoaded('student') ? $this->student->count() : 0)
            ),

            'department' => new DepartmentResource(
                $this->whenLoaded('department')
            ),
        ];
    }
}
