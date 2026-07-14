<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseBlock;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstCourse = Course::where('subject_code', 'IT101')->first();
        $secondCourse = Course::where('subject_code', 'IT102')->first();
        $firstCourseBlock = CourseBlock::where('course_id', $firstCourse?->course_id)
            ->where('semester_id', 1)
            ->where('block_code', 'BSIT-1A')
            ->first();
        $secondCourseBlock = CourseBlock::where('course_id', $secondCourse?->course_id)
            ->where('semester_id', 1)
            ->where('block_code', 'BSIT-1A')
            ->first();
        $firstRoom = Room::where('name', 'Room 101')->first();
        $secondRoom = Room::where('name', 'Room 102')->first();

        if (!$firstCourseBlock || !$secondCourseBlock || !$firstRoom || !$secondRoom) {
            return;
        }

        $schedules = [
            [
                'course_block_id' => $firstCourseBlock->course_block_id,
                'room_id' => $firstRoom->room_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
                'schedule_type' => 'lecture',
                'start_time' => '13:00',
                'end_time' => '15:00',
            ],
            [
                'course_block_id' => $firstCourseBlock->course_block_id,
                'room_id' => $secondRoom->room_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
                'schedule_type' => 'laboratory',
                'start_time' => '15:00',
                'end_time' => '17:00',
            ],
            [
                'course_block_id' => $secondCourseBlock->course_block_id,
                'room_id' => $firstRoom->room_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
                'schedule_type' => 'lecture',
                'start_time' => '13:00',
                'end_time' => '15:00',
            ],
            [
                'course_block_id' => $secondCourseBlock->course_block_id,
                'room_id' => $secondRoom->room_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
                'schedule_type' => 'laboratory',
                'start_time' => '15:00',
                'end_time' => '17:00',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::firstOrCreate($schedule);
        }
    }
}
