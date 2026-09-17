<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Trait HasPermissions
 *
 * @property-read \App\Models\UserRole|null $roleAssignment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $directPermissions
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany directPermissions()
 * @mixin \App\Models\User
 */
trait HasPermissions
{
    protected ?Collection $cachedPermissions = null;

    /**
     * Check if the user has any of the specified roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $currentRole = strtolower($this->roleAssignment?->role?->role_name ?? '');

        if (empty($currentRole)) {
            return false;
        }

        if (is_array($roles)) {
            $normalized = array_map('strtolower', $roles);
            return in_array($currentRole, $normalized, true);
        }

        return $currentRole === strtolower($roles);
    }

    /**
     * Check if the user possesses the required permission(s).
     * Super-administrators automatically bypass all permission checks.
     */
    public function hasPermission(string|array $permissions): bool
    {
        // 1. Universal administrator bypass
        if ($this->hasRole('administrator')) {
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
     * Get all effective permission names (Role permissions + Direct user permissions).
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

        // 2. Direct permissions assigned specifically to this user_id
        $directPermissions = $this->relationLoaded('directPermissions')
            ? $this->directPermissions->where('pivot.is_granted', true)->pluck('permission_name')
            : $this->directPermissions()->wherePivot('is_granted', true)->pluck('permission_name');

        // 3. Combined unique permissions
        $effective = $rolePermissions->merge($directPermissions)->unique()->values();

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
