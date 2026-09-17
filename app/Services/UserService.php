<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createUser(array $data)
    {
        $user = $this->userRepository->findByUserId($data['user_id']);

        if (!$user) {
            $middleInitial = !empty($data['middle_initial'])
                ? strtoupper(preg_replace('/[^a-zA-Z]/', '', $data['middle_initial']))
                : null;

            return $this->userRepository->create([
                'user_id' => $data['user_id'],
                'first_name' => $data['first_name'],
                'last_name' => ucfirst(strtolower($data['last_name'])),
                'middle_initial' => $middleInitial ?: null,
                'suffix' => $data['suffix'] ?? null,
                'sex' => $data['sex'],
                'role' => 'student',
                'password' => Hash::make(strtolower(Str::ascii($data['last_name']))),
            ]);
        }

        return $user;
    }

    public function resetPassword(string $userId): array
    {
        $user = $this->userRepository->findByUserId($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['User account not found.'],
            ]);
        }

        if (empty($user->last_name)) {
            throw ValidationException::withMessages([
                'last_name' => ['User does not have a last name on file to reset password.'],
            ]);
        }

        $defaultPassword = strtolower(Str::ascii($user->last_name));

        $this->userRepository->update($userId, [
            'password' => Hash::make($defaultPassword),
        ]);

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return [
            'user' => $user,
            'default_password' => $defaultPassword,
        ];
    }
}

