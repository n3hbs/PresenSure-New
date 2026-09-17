<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    public function getRoleId(string $role_name)
    {
        return $this->roleRepository->getRoleId($role_name);
    }

    public function assignUserRole(string $user_id, int $role_id)
    {
        return $this->roleRepository->assignUserRole($user_id, $role_id);
    }

    public function getAllRolesWithPermissions()
    {
        return $this->roleRepository->getAllRolesWithPermissions();
    }

    public function getAllPermissionsGrouped()
    {
        $permissions = $this->roleRepository->getAllPermissions();

        return $permissions->groupBy(function ($permission) {
            $name = strtolower($permission->permission_name);
            if (str_starts_with($name, 'students.')) return 'students';
            if (str_starts_with($name, 'instructors.')) return 'instructors';
            if (str_starts_with($name, 'roles.')) return 'roles';
            if (str_starts_with($name, 'attendance.')) return 'attendance';
            if (str_starts_with($name, 'audit.')) return 'audit';
            if (
                str_starts_with($name, 'departments.') ||
                str_starts_with($name, 'programs.') ||
                str_starts_with($name, 'semesters.') ||
                str_starts_with($name, 'courses.') ||
                str_starts_with($name, 'facilities.') ||
                str_starts_with($name, 'schedules.') ||
                str_starts_with($name, 'academic.')
            ) {
                return 'academic';
            }

            $parts = explode('.', $permission->permission_name);
            return $parts[0] ?? 'general';
        });
    }

    public function syncRolePermissions(int $role_id, array $permission_ids)
    {
        return DB::transaction(function () use ($role_id, $permission_ids) {
            return $this->roleRepository->syncRolePermissions($role_id, $permission_ids);
        });
    }
}
