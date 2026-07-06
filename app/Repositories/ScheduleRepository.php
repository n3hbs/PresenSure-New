<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Schedule;
use App\Repositories\Interfaces\ScheduleRepositoryInterface;

final class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function create(array $data)
    {
        return Schedule::create($data);
    }
}
