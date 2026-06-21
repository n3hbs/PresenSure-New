<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface RoleRepositoryInterface
{
    public function getRoleId(string $role_name);
    public function assignUserRole(string $user_id, int $role_id);
}
