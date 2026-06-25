import { useTheme } from "@/Context/ThemeContext";
import { Moon, Sun } from "lucide-react";

export default function DarkModeToggle({ className = "" }) {
    const { dark, setDark } = useTheme();

    return (
        <button
            onClick={() => setDark(!dark)}
            aria-label={dark ? "Switch to light mode" : "Switch to dark mode"}
            className={`
                inline-flex items-center justify-center w-9 h-9 rounded-full
                bg-blue-50 dark:bg-blue-900/40
                text-blue-600 dark:text-blue-300
                hover:bg-blue-100 dark:hover:bg-blue-800/60
                transition-colors duration-200
                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                dark:focus:ring-offset-gray-900
                ${className}
            `}
        >
            {dark ? <Sun size={18} /> : <Moon size={18} />}
        </button>
    );
}
