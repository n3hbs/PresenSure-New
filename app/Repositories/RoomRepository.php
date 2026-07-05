<?php

namespace App\Repositories;

use App\Models\Room;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Override;

class RoomRepository implements RoomRepositoryInterface
{
    public function create(array $data)
    {
        return Room::create($data);
    }
}
