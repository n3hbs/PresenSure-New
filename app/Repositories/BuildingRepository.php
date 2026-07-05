<?php

namespace App\Repositories;

use App\Models\Building;
use App\Repositories\Interfaces\BuildingRepositoryInterface;

class BuildingRepository implements BuildingRepositoryInterface
{
    public function create(array $data)
    {
        return Building::create($data);
    }
}
