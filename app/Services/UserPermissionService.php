<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Interfaces\UserPermissionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserPermissionService
{
    public function __construct(
        private UserPermissionRepositoryInterface $userPermissionRepository
    ) {}

    /**
     * Get aggregated user permission data for resource formatting.
     */
    public function getUserPermissionData(string $userId): array
    {
        $user = $this->userPermissionRepository->getUserWithPermissions($userId);
        $role = $user->roleAssignment?->role;

        $rolePermissions = $role
            ? $role->permissions->map(fn($p) => [
                'permission_id' => $p->permission_id,
                'permission_name' => $p->permission_name,
                'description' => $p->description,
            ])
            : collect();

        $directPermissions = $user->directPermissions
            ->where('pivot.is_granted', true)
            ->map(fn($p) => [
                'permission_id' => $p->permission_id,
                'permission_name' => $p->permission_name,
                'description' => $p->description,
            ]);

        $inheritedIds = $rolePermissions->pluck('permission_id')->values()->all();
        $directIds = $directPermissions->pluck('permission_id')->values()->all();
        $effectiveIds = array_values(array_unique(array_merge($inheritedIds, $directIds)));

        return [
            'user_id' => $user->user_id,
            'full_name' => trim("{$user->first_name} {$user->middle_initial} {$user->last_name} {$user->suffix}"),
            'role_name' => $user->role_name,
            'inherited_permission_ids' => $inheritedIds,
            'direct_permission_ids' => $directIds,
            'effective_permission_ids' => $effectiveIds,
            'effective_permission_names' => $user->permissions->values()->all(),
        ];
    }

    /**
     * Sync direct permissions for a user in a database transaction.
     */
    public function syncUserPermissions(string $userId, array $permissionIds): array
    {
        return DB::transaction(function () use ($userId, $permissionIds) {
            $this->userPermissionRepository->syncUserPermissions($userId, $permissionIds);
            return $this->getUserPermissionData($userId);
        });
    }

    /**
     * Reset all direct permission overrides for a user in a database transaction.
     */
    public function resetUserPermissions(string $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $this->userPermissionRepository->resetUserPermissions($userId);
            return $this->getUserPermissionData($userId);
        });
    }

    /**
     * Search users by user_id or name.
     */
    public function searchUsers(string $query, int $limit = 15)
    {
        return $this->userPermissionRepository->searchUsers($query, $limit);
    }
}
