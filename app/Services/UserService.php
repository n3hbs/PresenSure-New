<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createUser(array $data)
    {
        $user = $this->userRepository->findByUserId($data['user_id']);

        if (!$user) {
            return $this->userRepository->create([
                'user_id' => $data['user_id'],
                'first_name' => $data['first_name'],
                'last_name' => ucfirst(strtolower($data['last_name'])),
                'middle_initial' => $data['middle_initial'] ?? null,
                'suffix' => $data['suffix'] ?? null,
                'sex' => $data['sex'],
                'role' => 'student',
                'password' => Hash::make(strtolower(Str::ascii($data['last_name']))),
            ]);
        }

        return $user;
    }
}
