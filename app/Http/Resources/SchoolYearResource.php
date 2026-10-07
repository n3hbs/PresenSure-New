<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolYearResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'school_year_id' => $this->school_year_id,
            'school_year_start' => $this->school_year_start instanceof \DateTimeInterface
                ? $this->school_year_start->format('Y-m-d')
                : $this->school_year_start,
            'school_year_end' => $this->school_year_end instanceof \DateTimeInterface
                ? $this->school_year_end->format('Y-m-d')
                : $this->school_year_end,
            'year_range' => $this->year_range,
            'deleted_at' => $this->deleted_at instanceof \DateTimeInterface
                ? $this->deleted_at->toISOString()
                : $this->deleted_at,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->toISOString()
                : $this->created_at,
            'semesters_count' => $this->whenCounted('semesters', $this->semesters_count),
        ];
    }
}
