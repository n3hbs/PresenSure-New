<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\Permission;
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
        return UserRole::firstOrCreate(
            ['user_id' => $user_id, 'role_id' => $role_id],
            ['assigned_at' => now()]
        );
    }

    public function getAllRolesWithPermissions()
    {
        return Role::with('permissions')
            ->withCount('userRole')
            ->get();
    }

    public function getAllPermissions()
    {
        return Permission::orderBy('permission_name')->get();
    }

    public function syncRolePermissions(int $role_id, array $permission_ids)
    {
        $role = Role::findOrFail($role_id);
        $role->permissions()->sync($permission_ids);
        return $role->load('permissions');
    }
}
