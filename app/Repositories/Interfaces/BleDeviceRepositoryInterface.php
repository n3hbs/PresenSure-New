<?php

namespace App\Repositories\Interfaces;

use App\Models\BleDevice;

interface BleDeviceRepositoryInterface
{
    public function findBleDeviceByPublicId(string $publicDeviceId): ?BleDevice;
}
