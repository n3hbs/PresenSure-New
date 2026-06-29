import { useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import {
    AcademicCapIcon,
    BookOpenIcon,
    BuildingOffice2Icon,
    CalendarDaysIcon,
    ChevronDownIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    HomeIcon,
    IdentificationIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    UsersIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";

import Logo from "@/assets/images/MainLogo.webp";

const mainLinks = [
    { label: "Dashboard", href: "/dashboard", icon: HomeIcon },
    { label: "Role", href: "/roles", icon: IdentificationIcon },
    { label: "Semesters", href: "/semesters", icon: CalendarDaysIcon },
    { label: "Department", href: "/departments", icon: BuildingOffice2Icon },
    { label: "Programs", href: "/programs", icon: AcademicCapIcon },
    { label: "Courses", href: "/courses", icon: BookOpenIcon },
    { label: "Schedules", href: "/schedules", icon: ClipboardDocumentListIcon },
    { label: "My Schedules", href: "/my-schedules", icon: ClockIcon },
    { label: "Records", href: "/records", icon: DocumentTextIcon },
    { label: "Audit Logs", href: "/audit-logs", icon: ShieldCheckIcon },
];

const userLinks = [
    { label: "Students", href: "/students", icon: AcademicCapIcon },
    { label: "Instructors", href: "/instructors", icon: UsersIcon },
];

export default function Sidebar({ collapsed = false, mobile = false, onClose }) {
    const { url } = usePage();
    const usersActive = userLinks.some((item) => url?.startsWith(item.href));
    const [usersOpen, setUsersOpen] = useState(usersActive);
    const showText = mobile || !collapsed;

    const isActive = (href) =>
        href === "/dashboard" ? url === href : url?.startsWith(href);

    const navClass = (active) =>
        `flex w-full items-center rounded-xl py-3 text-sm font-medium transition-all duration-200 ${
            active
                ? "bg-blue-50 text-blue-700 shadow-sm shadow-blue-100/70"
                : "text-gray-600 hover:bg-blue-50/70 hover:text-blue-700"
        } ${showText ? "gap-3 px-4" : "justify-center px-3"}`;

    const subNavClass = (active) =>
        `flex items-center rounded-xl py-2.5 text-sm transition-all duration-200 ${
            active
                ? "bg-blue-50 text-blue-700 font-medium"
                : "text-gray-500 hover:bg-gray-100 hover:text-gray-700"
        } ${showText ? "gap-3 px-4" : "justify-center px-3"}`;

    const handleLinkClick = () => {
        if (mobile) {
            onClose?.();
        }
    };

    return (
        <aside
            className={`flex h-screen flex-col bg-white shadow-xl shadow-blue-950/5 transition-all duration-300 ${
                showText ? "w-72" : "w-20"
            }`}
        >
            <div
                className={`flex h-20 items-center ${
                    showText ? "justify-between px-6" : "justify-center px-3"
                }`}
            >
                <div
                    className={`flex items-center ${
                        showText ? "" : "justify-center"
                    }`}
                >
                    <img
                        src={Logo}
                        alt="PresenSure Logo"
                        className="h-11 w-11 object-contain"
                    />
                    {showText && (
                        <div className="ml-3">
                            <h1 className="text-xl font-bold tracking-tight text-gray-900">
                                Presen
                                <span className="text-blue-600">Sure</span>
                            </h1>
                            <p className="text-xs font-medium text-gray-400">
                                Smart Attendance
                            </p>
                        </div>
                    )}
                </div>

                {mobile && (
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                        aria-label="Close sidebar"
                    >
                        <XMarkIcon className="h-5 w-5" />
                    </button>
                )}
            </div>

            <nav
                className={`flex-1 space-y-1.5 overflow-y-auto pb-6 ${
                    showText ? "px-4" : "px-3"
                }`}
            >
                <Link
                    href="/dashboard"
                    className={navClass(isActive("/dashboard"))}
                    onClick={handleLinkClick}
                    title={!showText ? "Dashboard" : undefined}
                >
                    <HomeIcon className="h-5 w-5 shrink-0" />
                    {showText && <span>Dashboard</span>}
                </Link>

                <button
                    type="button"
                    onClick={() => setUsersOpen((open) => !open)}
                    className={navClass(usersActive)}
                    title={!showText ? "Users" : undefined}
                >
                    <UserGroupIcon className="h-5 w-5 shrink-0" />
                    {showText && (
                        <>
                            <span className="flex-1 text-left">Users</span>
                            <ChevronDownIcon
                                className={`h-4 w-4 transition-transform ${
                                    usersOpen ? "rotate-180" : ""
                                }`}
                            />
                        </>
                    )}
                </button>

                {usersOpen && (
                    <div className={`space-y-1 ${showText ? "pl-6" : ""}`}>
                        {userLinks.map(({ label, href, icon: Icon }) => (
                            <Link
                                key={href}
                                href={href}
                                className={subNavClass(isActive(href))}
                                onClick={handleLinkClick}
                                title={!showText ? label : undefined}
                            >
                                <Icon className="h-4 w-4 shrink-0" />
                                {showText && <span>{label}</span>}
                            </Link>
                        ))}
                    </div>
                )}

                <div className="pt-2">
                    {mainLinks.slice(1).map(({ label, href, icon: Icon }) => (
                        <Link
                            key={href}
                            href={href}
                            className={navClass(isActive(href))}
                            onClick={handleLinkClick}
                            title={!showText ? label : undefined}
                        >
                            <Icon className="h-5 w-5 shrink-0" />
                            {showText && <span>{label}</span>}
                        </Link>
                    ))}
                </div>
            </nav>
        </aside>
    );
}
