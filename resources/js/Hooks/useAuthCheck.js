import { useState, useEffect, useCallback, useRef } from "react";
import api from "@/Services/api";

export default function useAuthCheck({
    checkOnMount = true,
    checkOnFocus = true,
    intervalMs = 5 * 60 * 1000, // 5 minutes
} = {}) {
    const [isSessionExpired, setIsSessionExpired] = useState(false);
    const isCheckingRef = useRef(false);

    const checkAuth = useCallback(async () => {
        if (isCheckingRef.current) return;

        const token = sessionStorage.getItem("token");
        if (!token) {
            setIsSessionExpired(true);
            return false;
        }

        try {
            isCheckingRef.current = true;
            await api.get("/user/auth-check");
            setIsSessionExpired(false);
            return true;
        } catch (error) {
            if (error.response?.status === 401) {
                setIsSessionExpired(true);
            }
            return false;
        } finally {
            isCheckingRef.current = false;
        }
    }, []);

    useEffect(() => {
        // Handle global 401 event broadcasted by api interceptor
        const handleAuthExpired = () => {
            setIsSessionExpired(true);
        };

        window.addEventListener("ps:auth-expired", handleAuthExpired);

        if (checkOnMount) {
            checkAuth();
        }

        // Re-check when window regains focus
        const handleFocus = () => {
            if (checkOnFocus && !isSessionExpired) {
                checkAuth();
            }
        };

        window.addEventListener("focus", handleFocus);

        // Optional periodic heartbeat check
        let intervalId;
        if (intervalMs > 0) {
            intervalId = setInterval(() => {
                if (!isSessionExpired) {
                    checkAuth();
                }
            }, intervalMs);
        }

        return () => {
            window.removeEventListener("ps:auth-expired", handleAuthExpired);
            window.removeEventListener("focus", handleFocus);
            if (intervalId) clearInterval(intervalId);
        };
    }, [checkAuth, checkOnMount, checkOnFocus, intervalMs, isSessionExpired]);

    return {
        isSessionExpired,
        checkAuth,
    };
}
