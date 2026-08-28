<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PeriodRepository;
use Illuminate\Validation\ValidationException;

final class PeriodService
{
    public function __construct(
        protected PeriodRepository $periodRepository
    ) {}

    public function create(array $data)
    {
        return $this->periodRepository->create([
            'semester_id' => $data['semester_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
        ]);
    }

    /**
     * Give the attendance-session flow the academic period active today.
     * The repository owns the date-based database query.
     */
    public function getActivePeriod()
    {
        $period = $this->periodRepository->getActivePeriod();

        if (! $period) {
            throw ValidationException::withMessages([
                'period_id' => [
                    'No active period found.',
                ],
            ]);
        }

        return $period;
    }
}
