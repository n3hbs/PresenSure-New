<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface UserPermissionRepositoryInterface
{
    public function getUserWithPermissions(string $userId);
    public function syncUserPermissions(string $userId, array $permissionIds);
    public function resetUserPermissions(string $userId);
    public function searchUsers(string $query, int $limit = 15);
}
