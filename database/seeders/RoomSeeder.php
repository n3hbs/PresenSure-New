<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $building = Building::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Building']
        );

        $rooms = [
            [
                'building_id' => $building->building_id,
                'name' => 'Room 101',
                'floor_no' => 1,
                'capacity' => 40,
                'status' => 'Active',
            ],
            [
                'building_id' => $building->building_id,
                'name' => 'Room 102',
                'floor_no' => 1,
                'capacity' => 40,
                'status' => 'Active',
            ],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                [
                    'building_id' => $room['building_id'],
                    'name' => $room['name'],
                ],
                [
                    'floor_no' => $room['floor_no'],
                    'capacity' => $room['capacity'],
                    'status' => $room['status'],
                ]
            );
        }
    }
}
