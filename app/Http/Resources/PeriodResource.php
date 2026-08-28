<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period_id' => $this->period_id,
            'semester_id' => $this->semester_id,
            'name' => $this->name,
            'description' => $this->description,
            'period_start' => $this->period_start?->format('Y-m-d') ?? $this->period_start,
            'period_end' => $this->period_end?->format('Y-m-d') ?? $this->period_end,
            'semester' => new SemesterResource(
                $this->whenLoaded('semester')
            ),
        ];
    }
}
