<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleDaysSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = Schedule::orderBy('schedule_id')->take(4)->get();
        $dayPatterns = [
            ['monday', 'wednesday'],
            ['tuesday', 'thursday'],
            ['monday', 'wednesday'],
            ['tuesday', 'thursday'],
        ];

        foreach ($schedules as $index => $schedule) {
            foreach ($dayPatterns[$index] as $day) {
                ScheduleDay::firstOrCreate(
                    [
                        'schedule_id' => $schedule->schedule_id,
                        'day' => $day,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }
        }
    }
}
