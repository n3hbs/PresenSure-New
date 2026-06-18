<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findCredentials(array $data) {
        return User::firstWhere('user_id', $data['user_id'])->first();
    }
}
