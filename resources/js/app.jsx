import "../css/app.css";
import "./bootstrap";
import "./echo";
import { createInertiaApp } from "@inertiajs/react";
import { QueryClientProvider } from "@tanstack/react-query";
import { createRoot } from "react-dom/client";
import { ThemeProvider } from "@/Context/ThemeContext";
import queryClient from "@/Services/queryClient";

const appName = import.meta.env.VITE_APP_NAME || "PresenSure";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.jsx", { eager: true });
        const pageModule = pages[`./Pages/${name}.jsx`];

        if (!pageModule) {
            console.error(`Page component not found: ./Pages/${name}.jsx`);
            return null; 
        }

        return pageModule.default; // Cleanly return the default page export
    },

    setup({ el, App, props }) {
        createRoot(el).render(
            <QueryClientProvider client={queryClient}>
                <ThemeProvider>
                    <App {...props} />
                </ThemeProvider>
            </QueryClientProvider>
        );
    },
});
