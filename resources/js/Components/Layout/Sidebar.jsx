import { useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import {
    HomeIcon,
    UserGroupIcon,
    AcademicCapIcon,
    IdentificationIcon,
    CalendarDaysIcon,
    BuildingOffice2Icon,
    BookOpenIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
} from "@heroicons/react/24/solid";

import { ChevronDownIcon, ChevronRightIcon } from "@heroicons/react/24/outline";

export default function Sidebar() {
    const { url } = usePage();
    const [studentOpen, setStudentOpen] = useState(
        url?.startsWith("/students") || url?.startsWith("/instructors"),
    );

    const menuClass = (path) =>
        `flex items-center gap-3 px-4 py-3 rounded-lg transition ${
            url?.startsWith(path)
                ? "bg-blue-600 text-white"
                : "text-gray-700 hover:bg-gray-100"
        }`;

    return (
        <aside className="w-72 h-screen bg-white border-r shadow-sm flex flex-col">
            <div className="h-16 flex items-center px-6 border-b">
                <h1 className="text-xl font-bold">
                    Presen<span className="text-blue-600">Sure</span>
                </h1>
            </div>

            <nav className="flex-1 overflow-y-auto p-3 space-y-1">
                {/* Dashboard */}
                <Link href="/dashboard" className={menuClass("/dashboard")}>
                    <HomeIcon className="h-5 w-5" />
                    Dashboard
                </Link>

                {/* Users */}
                <button
                    onClick={() => setStudentOpen(!studentOpen)}
                    className="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-blue-50 transition"
                >
                    <div className="flex items-center gap-3">
                        <UserGroupIcon className="h-5 w-5" />
                        Users
                    </div>

                    <ChevronDownIcon
                        className={`h-4 w-4 transition ${
                            studentOpen ? "rotate-180" : ""
                        }`}
                    />
                </button>

                {studentOpen && (
                    <div className="ml-8 space-y-1">
                        <button
                            onClick={() => setStudentOpen(!studentOpen)}
                            className={`w-full flex items-center justify-between px-4 py-3 rounded-lg transition ${
                                usersActive
                                    ? "bg-blue-600 text-white"
                                    : "text-gray-700 hover:bg-gray-100"
                            }`}
                        >
                            <div className="flex items-center gap-3">
                                <UserGroupIcon className="h-5 w-5" />
                                Users
                            </div>

                            <ChevronDownIcon
                                className={`h-4 w-4 transition ${
                                    studentOpen ? "rotate-180" : ""
                                }`}
                            />
                        </button>
                    </div>
                )}

                {/* Role */}
                <Link href="/roles" className={menuClass("/roles")}>
                    <IdentificationIcon className="h-5 w-5" />
                    Role
                </Link>

                {/* Semesters */}
                <Link href="/semesters" className={menuClass("/semesters")}>
                    <CalendarDaysIcon className="h-5 w-5" />
                    Semesters
                </Link>

                {/* Department */}
                <Link href="/departments" className={menuClass("/departments")}>
                    <BuildingOffice2Icon className="h-5 w-5" />
                    Department
                </Link>

                {/* Programs */}
                <Link href="/programs" className={menuClass("/programs")}>
                    <AcademicCapIcon className="h-5 w-5" />
                    Programs
                </Link>

                {/* Courses */}
                <Link href="/courses" className={menuClass("/courses")}>
                    <BookOpenIcon className="h-5 w-5" />
                    Courses
                </Link>

                {/* Schedules */}
                <Link href="/schedules" className={menuClass("/schedules")}>
                    <ClipboardDocumentListIcon className="h-5 w-5" />
                    Schedules
                </Link>

                {/* My Schedules */}
                <Link
                    href="/my-schedules"
                    className={menuClass("/my-schedules")}
                >
                    <ClockIcon className="h-5 w-5" />
                    My Schedules
                </Link>

                {/* Records */}
                <Link href="/records" className={menuClass("/records")}>
                    <DocumentTextIcon className="h-5 w-5" />
                    Records
                </Link>

                {/* Audit Logs */}
                <Link href="/audit-logs" className={menuClass("/audit-logs")}>
                    <ShieldCheckIcon className="h-5 w-5" />
                    Audit Logs
                </Link>
            </nav>
        </aside>
    );
}
