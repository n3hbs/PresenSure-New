<?php

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
            'school_year_start' => $this->school_year_start,
            'school_year_end' => $this->school_year_end,
        ];
    }
}
