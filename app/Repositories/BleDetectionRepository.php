<?php

namespace App\Repositories;

use App\Models\BleDetection;
use App\Repositories\Interfaces\BleDetectionRepositoryInterface;

class BleDetectionRepository implements BleDetectionRepositoryInterface
{
    public function create(array $data)
    {
        return BleDetection::create($data);
    }
}
