<?php

namespace App\Repositories\Interfaces;

interface UserCourseBlockRepositoryInterface
{
    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool;
}
