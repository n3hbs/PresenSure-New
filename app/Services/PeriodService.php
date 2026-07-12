<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PeriodRepository;

final class PeriodService
{
    public function __construct(
        protected PeriodRepository $periodRepository
    ) {}

    public function createPeriod(array $data)
    {
        return $this->periodRepository->create([
            'semester_id' => $data['semester_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
        ]);
    }
}
