import { useState } from "react";
import Sidebar from "./Sidebar";
import TopNavbar from "./Navbar"; // Fixed import filename alignment

export default function MainLayout({ children }) {
    const [mobileOpen, setMobileOpen] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    const handleMenu = () => {
        if (window.innerWidth >= 1024) {
            setSidebarCollapsed((collapsed) => !collapsed);
            return;
        }

        setMobileOpen(true);
    };

    return (
        <div className="flex h-screen bg-gray-100">
            {/* Desktop Sidebar */}
            <div className="hidden lg:block">
                <Sidebar collapsed={sidebarCollapsed} />
            </div>

            {/* Mobile Sidebar */}
            {mobileOpen && (
                <div className="fixed inset-0 z-50 flex">
                    <Sidebar
                        mobile
                        onClose={() => setMobileOpen(false)}
                    />
                    <div
                        className="flex-1 bg-black/50"
                        onClick={() => setMobileOpen(false)}
                    />
                </div>
            )}

            <div className="flex flex-1 flex-col">
                <TopNavbar onMenu={handleMenu} />

                <main className="flex-1 overflow-auto p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
