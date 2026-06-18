<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Override;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data)
    {
        return User::create($data);
    }
    public function findByUserId(string $userId)
    {
        return User::where(
            'user_id',
            $userId
        )->first();
    }
}
