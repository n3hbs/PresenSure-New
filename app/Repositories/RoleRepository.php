<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Models\UserRole;
use App\Repositories\Interfaces\RoleRepositoryInterface;

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

        // Safeguard: System administrator role must never lose core roles management permissions
        if ($role->is_system_admin || strtolower($role->role_name) === 'administrator') {
            $criticalPermissionIds = Permission::whereIn('permission_name', [
                'roles.manage',
                'roles.view',
            ])->pluck('permission_id')->toArray();

            $permission_ids = array_values(array_unique(array_merge($permission_ids, $criticalPermissionIds)));
        }

        $role->permissions()->sync($permission_ids);

        return $role->load('permissions')->loadCount('userRole');
    }
}
