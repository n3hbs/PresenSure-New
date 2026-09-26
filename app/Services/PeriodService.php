<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Interfaces\PeriodRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PeriodService
{
    public function __construct(
        protected PeriodRepositoryInterface $periodRepository
    ) {}

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->periodRepository->create([
                'semester_id' => $data['semester_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
            ]);
        });
    }

    /**
     * Give the attendance-session flow the academic period active today.
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

    /**
     * Delete a period ensuring no attendance data is affected.
     */
    public function deletePeriod(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->periodRepository->delete($id);
        });
    }
}
