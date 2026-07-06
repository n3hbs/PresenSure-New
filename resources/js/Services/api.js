import axios from "axios";

const appUrl = import.meta.env.VITE_APP_URL?.replace(/\/$/, "") || "";

const api = axios.create({
    baseURL: `${appUrl}/api`,
    headers: {
        Accept: "application/json",
    },
});

export default api;
