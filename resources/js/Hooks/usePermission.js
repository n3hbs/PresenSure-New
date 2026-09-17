import { useState, useEffect, useCallback } from "react";
import { getStoredUser, getAuthToken, setAuthSession } from "@/Services/auth";
import api from "@/Services/api";

/**
 * Custom React hook for evaluating user permissions and role authorization.
 */
export function usePermission() {
    const [user, setUser] = useState(() => getStoredUser());

    useEffect(() => {
        const handleStorageChange = () => {
            setUser(getStoredUser());
        };

        window.addEventListener("storage", handleStorageChange);
        return () => window.removeEventListener("storage", handleStorageChange);
    }, []);

    const roleName = (user?.role_name || user?.role?.role_name || "").toLowerCase();
    const permissions = Array.isArray(user?.permissions) ? user.permissions : [];

    /**
     * Check if the user has any of the specified roles.
     * @param {string|string[]} roles
     * @returns {boolean}
     */
    const hasRole = useCallback(
        (roles) => {
            if (!roleName) return false;
            if (Array.isArray(roles)) {
                return roles.map((r) => r.toLowerCase()).includes(roleName);
            }
            return roleName === roles.toLowerCase();
        },
        [roleName]
    );

    /**
     * Check if user has permission(s). Universal bypass for 'administrator'.
     * @param {string|string[]} permission
     * @returns {boolean}
     */
    const can = useCallback(
        (permission) => {
            // Super-administrator bypass
            if (hasRole("administrator")) return true;

            if (Array.isArray(permission)) {
                return permission.some((p) => permissions.includes(p));
            }
            return permissions.includes(permission);
        },
        [hasRole, permissions]
    );

    /**
     * Check if user has ALL specified permissions.
     * @param {string[]} requiredPermissions
     * @returns {boolean}
     */
    const canAll = useCallback(
        (requiredPermissions) => {
            if (hasRole("administrator")) return true;
            if (!Array.isArray(requiredPermissions)) return can(requiredPermissions);
            return requiredPermissions.every((p) => permissions.includes(p));
        },
        [hasRole, permissions, can]
    );

    /**
     * Re-fetch authenticated user from /api/user/me and sync with storage.
     */
    const refreshPermissions = useCallback(async () => {
        try {
            const token = getAuthToken();
            if (!token) return null;

            const res = await api.get("/user/me");
            const freshUser = res.data?.data;
            if (freshUser) {
                setAuthSession(token, freshUser);
                setUser(freshUser);
            }
            return freshUser;
        } catch (error) {
            console.error("Failed to refresh user permissions:", error);
            return null;
        }
    }, []);

    return {
        user,
        roleName,
        permissions,
        hasRole,
        can,
        canAll,
        refreshPermissions,
    };
}

export default usePermission;
