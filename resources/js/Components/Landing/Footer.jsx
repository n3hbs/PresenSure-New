import { Fingerprint } from "lucide-react";

const LINKS = {
    Product: ["Features", "How It Works", "Pricing", "Changelog"],
    Company: ["About", "Blog", "Careers", "Contact"],
    Legal: ["Privacy Policy", "Terms of Service", "Cookie Policy"],
};

export default function Footer() {
    return (
        <footer className="bg-white dark:bg-gray-950 border-t border-gray-100 dark:border-gray-800">
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-10">
                    {/* Brand */}
                    <div className="col-span-2 md:col-span-1 flex flex-col gap-4">
                        <a
                            href="/"
                            className="flex items-center gap-2 font-bold text-gray-900 dark:text-white"
                        >
                            <span className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                <Fingerprint size={18} />
                            </span>
                            Presen<span className="text-blue-600">Sure</span>
                        </a>
                        <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Hands-free attendance powered by BLE and face
                            recognition.
                        </p>
                    </div>

                    {/* Links */}
                    {Object.entries(LINKS).map(([group, items]) => (
                        <div key={group}>
                            <p className="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                {group}
                            </p>
                            <ul className="space-y-2.5">
                                {items.map((item) => (
                                    <li key={item}>
                                        <a
                                            href="#"
                                            className="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                        >
                                            {item}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                <div className="mt-12 pt-8 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p className="text-xs text-gray-400 dark:text-gray-600">
                        © {new Date().getFullYear()} AttendIQ. All rights
                        reserved.
                    </p>
                    <p className="text-xs text-gray-400 dark:text-gray-600">
                        Built with BLE · Face Recognition · Laravel + React
                    </p>
                </div>
            </div>
        </footer>
    );
}
