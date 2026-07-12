<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface ScheduleRepositoryInterface
{
    public function create(array $data);

    public function createScheduleDay(array $data);
}
