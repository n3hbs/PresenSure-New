<?php

namespace Database\Seeders;

use App\Models\BleDevice;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BleDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $room101 = Room::where('name', 'Room 101')->firstOrFail();
        $room102 = Room::where('name', 'Room 102')->firstOrFail();

        $devices = [
            [
                'public_device_id' => 'ESP32-ROOM-101-001',
                'device_name' => 'PresenSure-Room-101',
                'room_id' => $room101->room_id,
                'device_secret' => str_repeat('a', 64),
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'public_device_id' => 'ESP32-ROOM-102-001',
                'device_name' => 'PresenSure-Room-102',
                'room_id' => $room102->room_id,
                'device_secret' => str_repeat('b', 64),
                'status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($devices as $device) {
            BleDevice::updateOrCreate(
                [
                    'public_device_id' => $device['public_device_id'],
                ],
                $device
            );
        }
    }
}
