import { useState } from "react";
import { Menu, X, Fingerprint } from "lucide-react";
import DarkModeToggle from "@/Components/UI/DarkModeToggle";
import Logo from "@/assets/images/whiteLogo.webp";
import Button from "@/Components/UI/Button";
import { Link } from "@inertiajs/react";

const NAV_LINKS = [
    { label: "Features", href: "#features" },
    { label: "How It Works", href: "#how-it-works" },
    { label: "About", href: "#about" },
];

export default function Navbar() {
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <header className="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800">
            <nav className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                {/* Logo */}
                <a
                    href="/"
                    className="flex items-center gap-2 font-bold text-gray-900 dark:text-white"
                >
                    <span className="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center overflow-hidden">
                        <img
                            src={Logo}
                            alt="PresenSure Logo"
                            className="w-8 h-8 object-contain"
                        />
                    </span>

                    <span>
                        Presen<span className="text-blue-600">Sure</span>
                    </span>
                </a>
                {/* Actions */}
                <div className="flex items-center gap-3">
                    <ul className="hidden md:flex items-center gap-6">
                        {NAV_LINKS.map((link) => (
                            <li key={link.href}>
                                <a
                                    href={link.href}
                                    className="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>

                    <Link href="/signIn">
                        <Button size="sm">Sign In</Button>
                    </Link>

                    {/* Mobile menu button */}
                    <button
                        className="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                        onClick={() => setMobileOpen(!mobileOpen)}
                        aria-label="Toggle menu"
                    >
                        {mobileOpen ? <X size={20} /> : <Menu size={20} />}
                    </button>
                    <DarkModeToggle />
                </div>
            </nav>

            {/* Mobile menu */}
            {mobileOpen && (
                <div className="md:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-4 space-y-3">
                    {NAV_LINKS.map((link) => (
                        <a
                            key={link.href}
                            href={link.href}
                            onClick={() => setMobileOpen(false)}
                            className="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            {link.label}
                        </a>
                    ))}
                    <div className="flex gap-3 pt-2">
                        <a href="/login" className="flex-1">
                            <Button
                                variant="outline"
                                size="sm"
                                className="w-full"
                            >
                                Log in
                            </Button>
                        </a>
                        <a href="/register" className="flex-1">
                            <Button size="sm" className="w-full">
                                Get Started
                            </Button>
                        </a>
                    </div>
                </div>
            )}
        </header>
    );
}
