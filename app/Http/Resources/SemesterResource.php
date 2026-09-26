<?php

declare(strict_types=1);

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
        $activePeriod = $this->active_period;

        return [
            'semester_id' => $this->semester_id,
            'school_year_id' => $this->school_year_id,
            'term' => $this->term,
            'semester_start' => $this->semester_start instanceof \DateTimeInterface
                ? $this->semester_start->format('Y-m-d')
                : $this->semester_start,
            'semester_end' => $this->semester_end instanceof \DateTimeInterface
                ? $this->semester_end->format('Y-m-d')
                : $this->semester_end,
            'is_active' => (bool) $this->is_active,
            'status' => $this->computed_status ?? $this->status ?? 'inactive',
            'remarks' => $this->remarks,
            'active_period' => $activePeriod ? [
                'period_id' => $activePeriod->period_id,
                'name' => $activePeriod->name,
                'period_start' => $activePeriod->period_start instanceof \DateTimeInterface
                    ? $activePeriod->period_start->format('Y-m-d')
                    : (string) $activePeriod->period_start,
                'period_end' => $activePeriod->period_end instanceof \DateTimeInterface
                    ? $activePeriod->period_end->format('Y-m-d')
                    : (string) $activePeriod->period_end,
            ] : null,
            'periods' => PeriodResource::collection(
                $this->whenLoaded('periods')
            ),
            'school_year' => new SchoolYearResource(
                $this->whenLoaded('schoolYear')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
