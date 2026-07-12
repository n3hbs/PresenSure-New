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
}
