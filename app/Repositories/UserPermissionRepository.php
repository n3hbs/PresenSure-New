<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserPermissionRepositoryInterface;

class UserPermissionRepository implements UserPermissionRepositoryInterface
{
    public function getUserWithPermissions(string $userId)
    {
        return User::with(['roleAssignment.role.permissions', 'directPermissions'])
            ->findOrFail($userId);
    }

    public function syncUserPermissions(string $userId, array $permissionIds)
    {
        $user = User::findOrFail($userId);

        $syncData = [];
        foreach ($permissionIds as $permId) {
            $syncData[$permId] = [
                'is_granted' => true,
                'assigned_at' => now(),
            ];
        }

        $user->directPermissions()->sync($syncData);
        $user->flushCachedPermissions();

        return $this->getUserWithPermissions($userId);
    }

    public function resetUserPermissions(string $userId)
    {
        $user = User::findOrFail($userId);
        $user->directPermissions()->detach();
        $user->flushCachedPermissions();

        return $this->getUserWithPermissions($userId);
    }

    public function searchUsers(string $query, int $limit = 20)
    {
        $cleanQuery = trim($query);
        $strippedId = str_replace(['-', ' '], '', $cleanQuery);

        return User::with(['roleAssignment.role', 'userProfile'])
            ->where(function ($q) use ($cleanQuery, $strippedId) {
                $q->where('user_id', 'like', "%{$cleanQuery}%")
                  ->orWhereRaw("REPLACE(user_id, '-', '') LIKE ?", ["%{$strippedId}%"])
                  ->orWhere('first_name', 'like', "%{$cleanQuery}%")
                  ->orWhere('last_name', 'like', "%{$cleanQuery}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$cleanQuery}%"]);
            })
            ->orderByRaw("
                CASE 
                    WHEN user_id = ? THEN 1
                    WHEN REPLACE(user_id, '-', '') = ? THEN 2
                    WHEN user_id LIKE ? THEN 3
                    WHEN REPLACE(user_id, '-', '') LIKE ? THEN 4
                    WHEN first_name LIKE ? OR last_name LIKE ? THEN 5
                    ELSE 6
                END ASC, user_id ASC
            ", [
                $cleanQuery,
                $strippedId,
                "{$cleanQuery}%",
                "{$strippedId}%",
                "{$cleanQuery}%",
                "{$cleanQuery}%",
            ])
            ->limit($limit)
            ->get();
    }
}
