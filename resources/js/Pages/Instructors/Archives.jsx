import { useEffect, useMemo, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    AcademicCapIcon,
    ArchiveBoxIcon,
    ArrowPathIcon,
    BuildingOffice2Icon,
    MagnifyingGlassIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DataTable from "@/Components/UI/DataTable";
import Modal from "@/Components/UI/Modal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import {
    archivedInstructorsQueryKey,
    instructorsQueryKey,
} from "@/Services/queryKeys";
import { notify } from "@/Services/toast";
import NoImage from "@/assets/images/noImage.webp";
import usePermission from "@/Hooks/usePermission";

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const normalizeInstructor = (record) => {
    const user = record.user || record || {};
    const instructor = Array.isArray(record.instructor)
        ? record.instructor[0]
        : record.instructor || {};
    const department = instructor.department || record.department || {};
    const profile = record.profile || user.profile || {};

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
        departmentName: department.department_name || "N/A",
        departmentCode: department.department_code || "N/A",
        image: profile.imagelink || profile.image_link || "",
        createdAt: user.created_at || record.created_at || null,
    };
};

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

export default function Archives() {
    const { can } = usePermission();
    const [search, setSearch] = useState("");
    const [restoreTarget, setRestoreTarget] = useState(null);
    const [restoring, setRestoring] = useState(false);
    const queryClient = useQueryClient();

    useEffect(() => {
        if (!can("instructors.archive")) {
            notify.error(
                "Access Denied",
                "You do not have permission to access archived instructors."
            );
            router.visit("/instructors");
        }
    }, [can]);

    const {
        data: archivedInstructors = [],
        isLoading: loading,
        isError,
        error,
    } = useQuery({
        queryKey: archivedInstructorsQueryKey,
        enabled: Boolean(getAuthToken()) && can("instructors.archive"),
        refetchOnMount: "always",
        queryFn: async () => {
            const token = sessionStorage.getItem("token");
            const response = await api.get("/instructor/archives", {
                headers: token
                    ? {
                          Authorization: `Bearer ${token}`,
                      }
                    : {},
            });

            return getCollection(response).map(normalizeInstructor);
        },
    });

    const isUnauthenticated = error?.response?.status === 401;
    const errorMessage = isUnauthenticated
        ? ""
        : error?.response?.data?.message || "Unable to load archived instructors right now.";

    useEffect(() => {
        if (isError && !isUnauthenticated && errorMessage) {
            notify.error("Unable to Load Archives", errorMessage);
        }
    }, [isError, isUnauthenticated, errorMessage]);

    const counts = useMemo(() => {
        const uniqueDepartments = new Set(
            archivedInstructors
                .map((instructor) => instructor.departmentName)
                .filter((dept) => dept && dept !== "N/A"),
        );
        const assigned = archivedInstructors.filter(
            (instructor) =>
                instructor.departmentName &&
                instructor.departmentName !== "N/A",
        ).length;

        return {
            total: archivedInstructors.length,
            departments: uniqueDepartments.size,
            assigned,
        };
    }, [archivedInstructors]);

    const filteredInstructors = useMemo(() => {
        const needle = search.trim().toLowerCase();
        if (!needle) return archivedInstructors;

        return archivedInstructors.filter((instructor) => {
            return [
                instructor.userId,
                instructor.fullName,
                instructor.departmentName,
                instructor.departmentCode,
            ]
                .join(" ")
                .toLowerCase()
                .includes(needle);
        });
    }, [archivedInstructors, search]);

    const handleRestore = async () => {
        if (!restoreTarget?.userId) return;
        setRestoring(true);
        try {
            const token = sessionStorage.getItem("token");
            await api.post(
                `/instructor/${restoreTarget.userId}/restore`,
                {},
                {
                    headers: token
                        ? {
                              Authorization: `Bearer ${token}`,
                          }
                        : {},
                },
            );

            notify.success(
                "Instructor Restored",
                `${restoreTarget.fullName} has been restored to active instructors.`,
            );

            queryClient.invalidateQueries({
                queryKey: archivedInstructorsQueryKey,
            });
            queryClient.invalidateQueries({
                queryKey: instructorsQueryKey,
            });
            setRestoreTarget(null);
        } catch (err) {
            const message =
                err.response?.data?.message ||
                "Failed to restore instructor. Please try again.";
            notify.error("Restore Failed", message);
        } finally {
            setRestoring(false);
        }
    };

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
                            alt={instructor.fullName}
                            className="h-full w-full object-cover"
                        />
                    )}
                </div>
            ),
        },
        {
            key: "userId",
            header: "Instructor ID",
            minWidth: "180px",
            render: (instructor) => (
                <span className="font-semibold text-gray-900">
                    {instructor.userId}
                </span>
            ),
        },
        {
            key: "fullName",
            header: "Full Name",
            minWidth: "260px",
            render: (instructor) => (
                <div>
                    <span className="font-semibold text-gray-900">
                        {instructor.fullName}
                    </span>
                    {instructor.departmentName !== "N/A" && (
                        <p className="text-xs text-gray-400">
                            {instructor.departmentName}
                        </p>
                    )}
                </div>
            ),
        },
        {
            key: "action",
            header: "Action",
            width: "160px",
            render: (instructor) => (
                <button
                    type="button"
                    onClick={() => setRestoreTarget(instructor)}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]"
                >
                    <ArrowPathIcon className="h-4 w-4" />
                    Restore Instructor
                </button>
            ),
        },
    ];

    const sortOptions = [
        {
            label: "Default",
            value: "default",
            sorter: (a, b) =>
                new Date(b.createdAt || 0) - new Date(a.createdAt || 0),
        },
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
    ];

    return (
        <>
            <Head title="Archived Instructors" />
            <div className="space-y-6">
                <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Instructors", href: "/instructors" },
                                { label: "Archived Instructors" },
                            ]}
                        />
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard
                        icon={ArchiveBoxIcon}
                        label="Total Archived"
                        value={counts.total}
                        tone="gray"
                    />
                    <StatCard
                        icon={BuildingOffice2Icon}
                        label="Departments"
                        value={counts.departments}
                        tone="blue"
                    />
                    <StatCard
                        icon={AcademicCapIcon}
                        label="Assigned"
                        value={counts.assigned}
                        tone="green"
                    />
                </div>

                <section className="rounded-xl bg-white p-4 shadow-sm shadow-blue-950/5">
                    <div>
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
                                placeholder="Search archived instructors by ID, name, or department..."
                                className="h-11 w-full rounded-xl bg-gray-50 pl-11 pr-4 text-sm text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
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
                    defaultSort="default"
                    pageSizeOptions={[10, 25, 50]}
                    emptyMessage="No archived instructors found."
                />

                <Modal
                    isOpen={Boolean(restoreTarget)}
                    onClose={() => {
                        if (!restoring) setRestoreTarget(null);
                    }}
                    title="Restore Instructor"
                    description="Are you sure you want to restore this instructor to active status?"
                    icon={<ArrowPathIcon className="h-6 w-6" />}
                    iconBg="bg-blue-50 text-blue-600"
                    maxWidth="md"
                    footer={
                        <div className="flex items-center justify-end gap-3">
                            <button
                                type="button"
                                disabled={restoring}
                                onClick={() => setRestoreTarget(null)}
                                className="rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                disabled={restoring}
                                onClick={handleRestore}
                                className="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]"
                            >
                                {restoring ? (
                                    <>
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                        Restoring...
                                    </>
                                ) : (
                                    <>
                                        <ArrowPathIcon className="h-4 w-4" />
                                        Restore Instructor
                                    </>
                                )}
                            </button>
                        </div>
                    }
                >
                    <p className="text-sm text-gray-600">
                        This will mark{" "}
                        <span className="font-semibold text-gray-900">
                            {restoreTarget?.fullName}
                        </span>{" "}
                        ({restoreTarget?.userId}) as{" "}
                        <span className="font-semibold text-emerald-600">Active</span> and
                        return them to the active instructor list.
                    </p>
                </Modal>
            </div>
        </>
    );
}

Archives.layout = (page) => <MainLayout>{page}</MainLayout>;
