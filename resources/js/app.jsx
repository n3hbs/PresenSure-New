import "../css/app.css";
import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";
import { ThemeProvider } from "@/Context/ThemeContext";

createInertiaApp({
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
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>
        );
    },
});