<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findCredentials(array $data) {
        return User::where('user_id', $data['user_id'])
        ->with(['roleAssignment.role', 'userProfile'])
        ->first();
    }
}
