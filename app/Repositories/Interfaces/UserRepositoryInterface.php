<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function create(array $data);
    public function findByUserId(string $user_id);
    public function isInstructor(string $userId): bool;
}
