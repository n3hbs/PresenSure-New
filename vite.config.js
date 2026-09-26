import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    darkMode: "class",

    content: [
        "./resources/js/**/*.{js,jsx,ts,tsx}",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
            },
            colors: {
                blue: {
                    50: "#EFF6FF",
                    100: "#DBEAFE",
                    200: "#BFDBFE",
                    300: "#93C5FD",
                    400: "#60A5FA",
                    500: "#3B82F6",
                    600: "#2563EB",
                    700: "#1D4ED8",
                    800: "#1E40AF",
                    900: "#1E3A8A",
                    950: "#172554",
                },
            },
            animation: {
                "ping-slow": "ping 2s cubic-bezier(0,0,0.2,1) infinite",
            },
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.jsx"],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("node_modules/react") || id.includes("node_modules/react-dom")) {
                        return "vendor-react";
                    }
                    if (id.includes("node_modules/@inertiajs")) {
                        return "vendor-inertia";
                    }
                    if (id.includes("node_modules/@tanstack/react-query")) {
                        return "vendor-query";
                    }
                    if (id.includes("node_modules/@heroicons/react")) {
                        return "vendor-icons";
                    }
                    if (id.includes("node_modules/laravel-echo") || id.includes("node_modules/pusher-js")) {
                        return "vendor-echo";
                    }
                },
            },
        },
    },
    server: {
        host: "0.0.0.0",
        hmr: {
            host: process.env.VITE_HMR_HOST || "192.168.1.10",
        },
    },
});
