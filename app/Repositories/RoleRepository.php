<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\UserRole;

class RoleRepository implements RoleRepositoryInterface
{
    public function getRoleId(string $role_name)
    {
        return Role::where('role_name', $role_name)
            ->value('role_id');
    }

    public function assignUserRole(string $user_id, int $role_id)
    {
        return UserRole::create([
            'user_id' => $user_id,
            'role_id' => $role_id,
            'assigned_at' => now(),
        ]);
    }
}
