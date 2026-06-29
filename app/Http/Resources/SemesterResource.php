<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'semester_id' => $this->semester_id,
            'term' => $this->term,
            'semester_start' => $this->semester_start,
            'semester_end' => $this->semester_end,
            'school_year' => new SchoolYearResource(
                $this->whenLoaded('schoolYear')
            ),
        ];
    }
}
