<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseBlock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseBlockSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstCourse = Course::firstOrCreate(
            ['subject_code' => 'IT101'],
            ['name' => 'Introduction to Computing']
        );

        $secondCourse = Course::firstOrCreate(
            ['subject_code' => 'IT102'],
            ['name' => 'Computer Programming 1']
        );

        $courseBlocks = [
            [
                'course_id' => $firstCourse->course_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
            ],
            [
                'course_id' => $secondCourse->course_id,
                'semester_id' => 1,
                'block_code' => 'BSIT-1A',
            ],
        ];

        foreach ($courseBlocks as $courseBlock) {
            CourseBlock::firstOrCreate($courseBlock);
        }
    }
}
