<?php

namespace App\Services;

use App\Repositories\BuildingRepository;

class BuildingService
{
    public function __construct(
        protected BuildingRepository $buildingRepository
    ) {}

    public function create(array $data)
    {
        return $this->buildingRepository->create([
            'code' => $data['code'],
            'name' => $data['name'],
        ]);
    }
}
