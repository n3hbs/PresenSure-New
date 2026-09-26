import "../css/app.css";
import "./bootstrap";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { QueryClientProvider } from "@tanstack/react-query";
import { createRoot } from "react-dom/client";
import { ThemeProvider } from "@/Context/ThemeContext";
import queryClient from "@/Services/queryClient";

const appName = import.meta.env.VITE_APP_NAME || "PresenSure";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob("./Pages/**/*.jsx")
        ),
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
