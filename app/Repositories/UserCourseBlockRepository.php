<?php

namespace App\Repositories;

use App\Models\UserCourseBlock;

class UserCourseBlockRepository
{
    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool
    {
        return UserCourseBlock::where('user_id', $user_id)
            ->where('course_block_id', $course_block_id)
            ->exists();
    }
}
