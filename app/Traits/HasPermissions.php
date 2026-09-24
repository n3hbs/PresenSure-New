<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Collection;

/**
 * Trait HasPermissions
 *
 * @property-read UserRole|null $roleAssignment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $directPermissions
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany directPermissions()
 *
 * @mixin User
 */
trait HasPermissions
{
    protected ?Collection $cachedPermissions = null;

    /**
     * Check if the user has any of the specified roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $role = $this->roleAssignment?->role;

        if (! $role) {
            return false;
        }

        $currentRoleName = strtolower($role->role_name ?? '');

        if (is_array($roles)) {
            $normalized = array_map('strtolower', $roles);

            return in_array($currentRoleName, $normalized, true);
        }

        return $currentRoleName === strtolower($roles);
    }

    /**
     * Check if the user possesses the required permission(s).
     * System administrators automatically bypass all permission checks.
     */
    public function hasPermission(string|array $permissions): bool
    {
        $role = $this->roleAssignment?->role;
        if ($role && ($role->is_system_admin || strtolower($role->role_name ?? '') === 'administrator')) {
            return true;
        }

        $userPermissions = $this->getPermissions();

        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if ($userPermissions->contains($permission)) {
                    return true;
                }
            }

            return false;
        }

        return $userPermissions->contains($permissions);
    }

    /**
     * Get all effective permission names (Role permissions + Direct user overrides).
     * Direct Denies (is_granted = false) override Role Grants.
     */
    public function getPermissions(): Collection
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        $role = $this->roleAssignment?->role;

        // 1. Permissions inherited from Role
        $rolePermissions = collect();
        if ($role) {
            $rolePermissions = $role->relationLoaded('permissions')
                ? $role->permissions->pluck('permission_name')
                : $role->permissions()->pluck('permission_name');
        }

        // 2. Fetch all direct overrides (Grants and Denies)
        $overrides = $this->relationLoaded('directPermissions')
            ? $this->directPermissions
            : $this->directPermissions()->get();

        $directGrants = $overrides->where('pivot.is_granted', true)->pluck('permission_name');
        $directDenies = $overrides->where('pivot.is_granted', false)->pluck('permission_name');

        // 3. Combined Logic: (Role Permissions - Denies) + Grants
        $effective = $rolePermissions
            ->reject(fn ($name) => $directDenies->contains($name))
            ->merge($directGrants)
            ->unique()
            ->values();

        return $this->cachedPermissions = $effective;
    }

    /**
     * Clear cached permissions on the user instance.
     */
    public function flushCachedPermissions(): void
    {
        $this->cachedPermissions = null;
    }
}
