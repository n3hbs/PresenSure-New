import axios from "axios";

const appUrl = import.meta.env.VITE_APP_URL?.replace(/\/$/, "") || "";

const api = axios.create({
    baseURL: `${appUrl}/api`,
    headers: {
        Accept: "application/json",
    },
});

// Automatically attach Bearer token if present in sessionStorage
api.interceptors.request.use(
    (config) => {
        try {
            const token = sessionStorage.getItem("token");
            if (token && !config.headers.Authorization) {
                config.headers.Authorization = `Bearer ${token}`;
            }
        } catch (e) {
            console.error("Error accessing token from storage:", e);
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Intercept 401 responses to trigger session expiration modal across the app
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (
            error.response?.status === 401 &&
            !error.config?.url?.includes("/user/signin")
        ) {
            window.dispatchEvent(new CustomEvent("ps:auth-expired"));
        }
        return Promise.reject(error);
    }
);

export default api;
