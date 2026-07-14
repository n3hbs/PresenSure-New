<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'subject_code' => 'IT101',
                'name' => 'Introduction to Computing',
            ],
            [
                'subject_code' => 'IT102',
                'name' => 'Computer Programming 1',
            ],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(
                ['subject_code' => $course['subject_code']],
                ['name' => $course['name']]
            );
        }
    }
}
