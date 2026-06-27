import { Bars3Icon, BellIcon } from "@heroicons/react/24/outline";

export default function TopNavbar({ onMenu }) {
    return (
        <header className="h-16 bg-white border-b flex items-center justify-between px-6">
            <div className="flex items-center gap-3">
                <button
                    onClick={onMenu}
                    className="lg:hidden p-2 rounded hover:bg-gray-100"
                >
                    <Bars3Icon className="h-6 w-6" />
                </button>

                <h2 className="text-xl font-semibold">Dashboard</h2>
            </div>

            <div className="flex items-center gap-5">
                <button className="relative p-2 rounded hover:bg-gray-100">
                    <BellIcon className="h-6 w-6" />

                    <span className="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500" />
                </button>

                <img
                    src="https://i.pravatar.cc/100"
                    className="w-10 h-10 rounded-full"
                />
            </div>
        </header>
    );
}
