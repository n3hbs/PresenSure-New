<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserRole;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Override;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data)
    {
        return User::create($data);
    }
    public function findByUserId(string $user_id)
    {
        return User::with('userProfile')
            ->where('user_id', $user_id)
            ->first();
    }

    public function isInstructor(string $userId): bool
    {
        return UserRole::where('user_id', $userId)
            ->whereHas('role', function ($query) {
                $query->where('role_name', 'instructor');
            })
            ->exists();
    }
}
