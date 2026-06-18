<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserProfileInterface;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;
use App\Models\UserProfile;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function updateOrCreateByUserId(string $userId, array $data)
    {
        return UserProfile::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }
}
