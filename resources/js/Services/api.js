import axios from "axios";

const api = axios.create({
    baseURL: "http://192.168.1.11:8000/api",
    headers: {
        Accept: "application/json",
    },
});

export default api;