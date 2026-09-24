<?php

namespace App\Repositories;

use App\Models\UserProfile;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function updateOrCreateByUserId(string $userId, array $data)
    {
        return UserProfile::updateOrCreate(
            ['user_id' => $userId], $data);
    }
}
