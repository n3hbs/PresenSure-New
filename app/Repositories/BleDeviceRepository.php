<?php

namespace App\Repositories;

use App\Models\BleDevice;

class BleDeviceRepository
{
    public function findBleDeviceByPublicId(string $publicDeviceId): ?BleDevice
    {
        return BleDevice::where('public_device_id', $publicDeviceId)->first();
    }
}
