<?php

namespace App\Services;

use App\Repositories\RoomRepository;

class RoomService
{
    public function __construct(
        protected RoomRepository $roomRepository
    ) {}

    public function createBuilding(array $data)
    {
        $this->roomRepository->create([
            'building_id' => $data['building_id'],
            'name' => $data['name'],
            'floor_no' => $data['floor_no'],
            'capacity' => $data['capacity'],
        ]);
    }
}
