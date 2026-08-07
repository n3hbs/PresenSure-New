import { useMemo, useState } from "react";
import { Link, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    ArchiveBoxIcon,
    ArrowRightIcon,
    MagnifyingGlassIcon,
    UserGroupIcon,
    UserPlusIcon,
    UsersIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Badge from "@/Components/UI/Badge";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DataTable from "@/Components/UI/DataTable";
import SelectDropdown from "@/Components/UI/SelectDropdown";
import api from "@/Services/api";
import { instructorsQueryKey } from "@/Services/queryKeys";
import NoImage from "@/assets/images/noImage.webp";

const allOption = { label: "All", value: "" };

const actionLinks = [
    {
        label: "Single Registration",
        href: "/instructors/single-registration",
        icon: UserPlusIcon,
    },
    {
        label: "View Archives",
        href: "/instructors/archives",
        icon: ArchiveBoxIcon,
    },
];

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const normalizeInstructor = (record) => {
    const user = record.user || record;
    const instructor = Array.isArray(record.instructor)
        ? record.instructor[0]
        : record.instructor || {};
    const department = instructor.department || record.department || {};
    const profile = record.profile || user.profile || {};
    const status = instructor.status || record.status || "Active";

    const fullName = [
        user.last_name,
        user.first_name,
        user.suffix,
        user.middle_initial,
    ]
        .filter(Boolean)
        .join(" ");

    return {
        id: user.user_id,
        userId: user.user_id || "N/A",
        firstName: user.first_name || "",
        lastName: user.last_name || "",
        middleInitial: user.middle_initial || "",
        suffix: user.suffix || "",
        fullName: fullName || "N/A",
        sex: user.sex || "N/A",
        status: status || "Active",
        departmentName: department.department_name || "N/A",
        departmentCode: department.department_code || "N/A",
        image: profile.imagelink || profile.image_link || "",
        isActive:
            !status ||
            ["active", "enrolled", "registered"].includes(
                String(status).toLowerCase(),
            ),
    };
};

const makeOptions = (items) => [
    allOption,
    ...Array.from(
        new Set(items.filter(Boolean).filter((item) => item !== "N/A")),
    )
        .sort((a, b) => String(a).localeCompare(String(b)))
        .map((item) => ({ label: item, value: item })),
];

const StatCard = ({ icon: Icon, label, value, tone = "blue" }) => {
    const tones = {
        blue: "bg-blue-50 text-blue-700",
        green: "bg-green-50 text-green-700",
        gray: "bg-gray-100 text-gray-600",
    };

    return (
        <div className="rounded-lg bg-white p-5 shadow-sm shadow-blue-950/5">
            <div className="flex items-center gap-4">
                <div
                    className={`flex h-12 w-12 items-center justify-center rounded-xl ${tones[tone]}`}
                >
                    <Icon className="h-6 w-6" />
                </div>
                <div>
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {label}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-gray-900">
                        {value}
                    </p>
                </div>
            </div>
        </div>
    );
};

export default function Instructors() {
    const [search, setSearch] = useState("");
    const [department, setDepartment] = useState("");
    const [activeTab, setActiveTab] = useState("active");
    const queryClient = useQueryClient();

    const {
        data: instructors = [],
        isLoading: loading,
        isError,
        error,
    } = useQuery({
        queryKey: instructorsQueryKey,
        queryFn: async () => {
            const token = sessionStorage.getItem("token");
            const response = await api.get("/instructors", {
                headers: token
                    ? {
                          Authorization: `Bearer ${token}`,
                      }
                    : {},
            });

            return getCollection(response).map(normalizeInstructor);
        },
    });

    const refreshInstructors = () => {
        queryClient.invalidateQueries({
            queryKey: instructorsQueryKey,
        });
    };

    const errorMessage =
        error?.response?.data?.message || "Unable to load instructors right now.";

    const counts = useMemo(() => {
        const active = instructors.filter(
            (instructor) => instructor.isActive,
        ).length;

        return {
            total: instructors.length,
            active,
            inactive: instructors.length - active,
        };
    }, [instructors]);

    const departmentOptions = useMemo(
        () =>
            makeOptions(
                instructors.map((instructor) => instructor.departmentName),
            ),
        [instructors],
    );

    const filteredInstructors = useMemo(() => {
        const needle = search.trim().toLowerCase();

        return instructors.filter((instructor) => {
            const matchesTab =
                activeTab === "active"
                    ? instructor.isActive
                    : !instructor.isActive;
            const matchesDepartment = department
                ? instructor.departmentName === department
                : true;
            const matchesSearch = needle
                ? [
                      instructor.userId,
                      instructor.fullName,
                      instructor.departmentName,
                      instructor.departmentCode,
                      instructor.sex,
                  ]
                      .join(" ")
                      .toLowerCase()
                      .includes(needle)
                : true;

            return matchesTab && matchesDepartment && matchesSearch;
        });
    }, [activeTab, department, search, instructors]);

    const columns = [
        {
            key: "profile",
            header: "Profile",
            width: "86px",
            render: (instructor) => (
                <div className="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                    {instructor.image ? (
                        <img
                            src={instructor.image}
                            alt={instructor.fullName}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <img
                            src={NoImage}
                            alt={NoImage}
                            className="h-full w-full object-cover"
                        />
                    )}
                </div>
            ),
        },
        {
            key: "fullName",
            header: "Full Name",
            minWidth: "240px",
            render: (instructor) => (
                <div>
                    <p className="font-semibold text-gray-900">
                        {instructor.fullName}
                    </p>
                    <p className="text-xs text-gray-400">{instructor.userId}</p>
                </div>
            ),
        },
        {
            key: "sex",
            header: "Sex",
            minWidth: "90px",
            render: (instructor) => (
                <span className="font-medium text-gray-700">
                    {instructor.sex}
                </span>
            ),
        },
        {
            key: "departmentName",
            header: "Department",
            minWidth: "180px",
            render: (instructor) => (
                <div>
                    <p className="font-medium text-gray-800">
                        {instructor.departmentName}
                    </p>
                    {instructor.departmentCode !== "N/A" && (
                        <p className="text-xs text-gray-400">
                            {instructor.departmentCode}
                        </p>
                    )}
                </div>
            ),
        },
        {
            key: "status",
            header: "Status",
            minWidth: "110px",
            render: (instructor) => (
                <Badge variant={instructor.isActive ? "success" : "secondary"}>
                    {instructor.status}
                </Badge>
            ),
        },
        {
            key: "action",
            header: "Action",
            width: "90px",
            render: (instructor) => (
                <button
                    type="button"
                    onClick={() =>
                        router.visit(
                            `/instructors/instructor-details?user_id=${instructor.userId}`,
                        )
                    }
                    className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700"
                    aria-label={`View ${instructor.fullName}`}
                >
                    <ArrowRightIcon className="h-4 w-4" />
                </button>
            ),
        },
    ];

    const sortOptions = [
        {
            label: "Name A-Z",
            value: "name_asc",
            sorter: (a, b) => a.fullName.localeCompare(b.fullName),
        },
        {
            label: "Name Z-A",
            value: "name_desc",
            sorter: (a, b) => b.fullName.localeCompare(a.fullName),
        },
        {
            label: "Instructor ID",
            value: "id_asc",
            sorter: (a, b) => a.userId.localeCompare(b.userId),
        },
        {
            label: "Department",
            value: "department_asc",
            sorter: (a, b) =>
                a.departmentName.localeCompare(b.departmentName),
        },
    ];

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Instructors" },
                        ]}
                    />
                </div>

                <div className="flex gap-2 overflow-x-auto pb-1">
                    {actionLinks.map(({ label, href, icon: Icon }) => (
                        <Link
                            key={href}
                            href={href}
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700"
                        >
                            <Icon className="h-4 w-4" />
                            <span className="hidden sm:inline">{label}</span>
                        </Link>
                    ))}
                </div>
            </div>

            {isError && (
                <div className="rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {errorMessage}
                </div>
            )}

            <div className="grid gap-4 md:grid-cols-3">
                <StatCard
                    icon={UserGroupIcon}
                    label="Total Instructors"
                    value={counts.total}
                />
                <StatCard
                    icon={UsersIcon}
                    label="Active"
                    value={counts.active}
                    tone="green"
                />
                <StatCard
                    icon={ArchiveBoxIcon}
                    label="Inactive"
                    value={counts.inactive}
                    tone="gray"
                />
            </div>

            <section className="rounded-xl bg-white p-4 shadow-sm shadow-blue-950/5">
                <div className="mb-4 flex gap-2 overflow-x-auto">
                    {[
                        { label: "Active", value: "active" },
                        { label: "Inactive", value: "inactive" },
                    ].map((tab) => (
                        <button
                            key={tab.value}
                            type="button"
                            onClick={() => setActiveTab(tab.value)}
                            className={`h-10 rounded-lg px-4 text-sm font-semibold transition ${
                                activeTab === tab.value
                                    ? "bg-blue-600 text-white shadow-sm shadow-blue-200"
                                    : "bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-700"
                            }`}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="flex items-end gap-3">
                    <div className="flex-1">
                        <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Search
                        </label>
                        <div className="relative">
                            <MagnifyingGlassIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Search instructors..."
                                className="h-11 w-full rounded-xl bg-gray-50 pl-11 pr-4 text-sm text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            />
                        </div>
                    </div>

                    <div className="w-48 sm:w-60 shrink-0">
                        <SelectDropdown
                            label="Department"
                            options={departmentOptions}
                            value={department}
                            onChange={setDepartment}
                        />
                    </div>
                </div>
            </section>

            <DataTable
                columns={columns}
                data={filteredInstructors}
                loading={loading}
                rowKey="userId"
                sortOptions={sortOptions}
                defaultSort="name_asc"
                pageSizeOptions={[10, 25, 50]}
                emptyMessage="No instructors match the current filters."
            />
        </div>
    );
}

Instructors.layout = (page) => <MainLayout>{page}</MainLayout>;
