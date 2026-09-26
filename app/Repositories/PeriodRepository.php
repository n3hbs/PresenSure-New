<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Period;
use App\Repositories\Interfaces\PeriodRepositoryInterface;
use Illuminate\Validation\ValidationException;

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

    /**
     * Delete a period ensuring no attendance records or sessions depend on it.
     *
     * @throws ValidationException
     */
    public function delete(int $id): bool
    {
        $period = Period::findOrFail($id);

        if ($period->attendanceSessions()->exists()) {
            throw ValidationException::withMessages([
                'period' => ['Cannot delete this period because it has existing attendance sessions recorded.'],
            ]);
        }

        return (bool) $period->delete();
    }
}
