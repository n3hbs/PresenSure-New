<?php

namespace Database\Seeders;

use App\Models\CourseBlock;
use App\Models\UserCourseBlock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserCourseBlockSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 'C-2022-0138';

        CourseBlock::query()
            ->where('semester_id', 1)
            ->where('block_code', 'BSIT-1A')
            ->get()
            ->each(function (CourseBlock $courseBlock) use ($userId): void {
                UserCourseBlock::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'course_block_id' => $courseBlock->course_block_id,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            });
    }
}
