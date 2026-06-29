import { useEffect, useMemo, useRef, useState } from "react";
import { router, usePage } from "@inertiajs/react";
import {
    Bars3Icon,
    ChevronDownIcon,
    DocumentCheckIcon,
    LockClosedIcon,
    PowerIcon,
} from "@heroicons/react/24/outline";

const pageTitles = [
    { path: "/dashboard", title: "Dashboard" },
    { path: "/students", title: "Students" },
    { path: "/instructors", title: "Instructors" },
    { path: "/roles", title: "Roles" },
    { path: "/semesters", title: "Semesters" },
    { path: "/departments", title: "Departments" },
    { path: "/programs", title: "Programs" },
    { path: "/courses", title: "Courses" },
    { path: "/schedules", title: "Schedules" },
    { path: "/my-schedules", title: "My Schedules" },
    { path: "/records", title: "Records" },
    { path: "/audit-logs", title: "Audit Logs" },
];

const getStoredUser = () => {
    try {
        const user = sessionStorage.getItem("user");
        return user ? JSON.parse(user) : null;
    } catch {
        return null;
    }
};

const getUserName = (user) => {
    if (!user) return "User";
    if (user.name) return user.name;

    const parts = [
        user.first_name,
        user.middle_initial,
        user.last_name,
        user.suffix,
    ].filter(Boolean);

    return parts.length ? parts.join(" ") : user.user_id || "User";
};

const getInitials = (name) => {
    const initials = name
        .split(" ")
        .filter(Boolean)
        .map((part) => part[0])
        .join("")
        .toUpperCase();

    return initials.slice(0, 2) || "U";
};

const getRole = (user) => {
    if (!user) return "Member";
    if (typeof user.role === "string") return user.role;
    if (user.role?.role_name) return user.role.role_name;
    if (user.role_name) return user.role_name;
    return "Member";
};

export default function TopNavbar({ onMenu }) {
    const { url, props } = usePage();
    const dropdownRef = useRef(null);
    const [dropdownOpen, setDropdownOpen] = useState(false);

    const user = useMemo(
        () => props.auth?.user ?? getStoredUser(),
        [props.auth?.user],
    );
    const userName = getUserName(user);
    const role = getRole(user);

    const pageTitle =
        pageTitles.find((item) => url?.startsWith(item.path))?.title ||
        "PresenSure";

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                setDropdownOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const handleLogout = () => {
        sessionStorage.removeItem("token");
        sessionStorage.removeItem("user");
        setDropdownOpen(false);
        router.visit("/signIn");
    };

    return (
        <header className="flex h-20 items-center justify-between bg-white/95 px-4 shadow-sm shadow-blue-950/5 backdrop-blur md:px-6">
            <div className="flex min-w-0 items-center gap-3">
                <button
                    type="button"
                    onClick={onMenu}
                    className="rounded-full p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"
                    aria-label="Toggle sidebar"
                >
                    <Bars3Icon className="h-6 w-6" />
                </button>

                <div className="min-w-0">
                    <h2 className="truncate text-xl font-bold text-gray-900">
                        {pageTitle} Management
                    </h2>
                </div>
            </div>

            <div className="flex items-center gap-3 md:gap-5">
                <div className="hidden text-right lg:block">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        Current Term
                    </p>
                    <p className="text-sm font-medium text-gray-700">
                        Semester status pending
                    </p>
                </div>

                <div className="relative" ref={dropdownRef}>
                    <button
                        type="button"
                        onClick={() => setDropdownOpen((open) => !open)}
                        className="flex items-center gap-2 rounded-2xl px-2 py-1.5 transition hover:bg-blue-50"
                        aria-expanded={dropdownOpen}
                    >
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm shadow-blue-200">
                            {getInitials(userName)}
                        </div>

                        <div className="hidden min-w-0 text-left md:block">
                            <p className="max-w-36 truncate text-sm font-semibold text-gray-800">
                                {userName}
                            </p>
                            <p className="text-xs capitalize text-gray-400">
                                {role}
                            </p>
                        </div>

                        <ChevronDownIcon
                            className={`hidden h-4 w-4 text-gray-400 transition-transform md:block ${
                                dropdownOpen ? "rotate-180" : ""
                            }`}
                        />
                    </button>

                    {dropdownOpen && (
                        <div className="absolute right-0 z-50 mt-3 w-60 rounded-2xl bg-white p-2 shadow-2xl shadow-blue-950/10">
                            <div className="px-3 py-3">
                                <p className="truncate text-sm font-semibold text-gray-900">
                                    {userName}
                                </p>
                                <p className="truncate text-xs capitalize text-gray-400">
                                    {role}
                                </p>
                            </div>

                            <button
                                type="button"
                                className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                <LockClosedIcon className="h-4 w-4" />
                                Change Password
                            </button>

                            <button
                                type="button"
                                className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                <DocumentCheckIcon className="h-4 w-4" />
                                Policy
                            </button>

                            <button
                                type="button"
                                onClick={handleLogout}
                                className="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                            >
                                <PowerIcon className="h-4 w-4" />
                                Log Out
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
