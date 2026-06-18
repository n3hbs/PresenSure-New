<?php

namespace App\Repositories\Interfaces;

interface UserProfileRepositoryInterface
{
    public function updateOrCreateByUserId(
        string $userId,
        array $data
    );
}
