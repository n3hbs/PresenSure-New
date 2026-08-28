<?php

namespace App\Services;

use App\Repositories\RoomRepository;

class RoomService
{
    public function __construct(
        protected RoomRepository $roomRepository
    ) {}

    public function create(array $data)
    {
        return $this->roomRepository->create([
            'building_id' => $data['building_id'],
            'name' => $data['name'],
            'floor_no' => $data['floor_no'],
            'capacity' => $data['capacity'],
        ]);
    }
}
