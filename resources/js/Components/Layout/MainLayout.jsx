import { useState } from "react";
import Sidebar from "./Sidebar";
import TopNavbar from "./Navbar"; // Fixed import filename alignment

export default function MainLayout({ children }) {
    const [open, setOpen] = useState(false);

    return (
        <div className="flex h-screen bg-gray-100">
            {/* Desktop Sidebar */}
            <div className="hidden lg:block">
                <Sidebar />
            </div>

            {/* Mobile Sidebar */}
            {open && (
                <div className="fixed inset-0 z-50 flex">
                    <Sidebar />
                    <div
                        className="flex-1 bg-black/50"
                        onClick={() => setOpen(false)}
                    />
                </div>
            )}

            <div className="flex flex-1 flex-col">
                <TopNavbar onMenu={() => setOpen(true)} />

                <main className="flex-1 overflow-auto p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}