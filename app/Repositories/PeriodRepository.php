<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Period;
use App\Repositories\Interfaces\PeriodRepositoryInterface;

final class PeriodRepository implements PeriodRepositoryInterface
{
    public function create(array $data)
    {
        return Period::create($data);
    }

    /**
     * Return the active period containing today, or fallback to the latest period.
     */
    public function getActivePeriod()
    {
        $period = Period::with(['semester.schoolYear'])
            ->whereDate('period_start', '<=', now()->toDateString())
            ->whereDate('period_end', '>=', now()->toDateString())
            ->first();

        if (! $period) {
            $period = Period::with(['semester.schoolYear'])
                ->orderBy('period_end', 'desc')
                ->first();
        }

        return $period;
    }
}
