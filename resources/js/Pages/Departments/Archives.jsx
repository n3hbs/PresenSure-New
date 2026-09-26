import { useEffect, useMemo, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
    AcademicCapIcon,
    ArchiveBoxIcon,
    ArrowLeftIcon,
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
import { departmentsQueryKey, archivedDepartmentsQueryKey } from "@/Services/queryKeys";
import { notify } from "@/Services/toast";
import usePermission from "@/Hooks/usePermission";

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const formatDate = (dateStr) => {
    if (!dateStr) return "—";
    try {
        if (typeof dateStr === "string" && /^\d{4}-\d{2}-\d{2}/.test(dateStr)) {
            const [y, m, d] = dateStr.slice(0, 10).split("-").map(Number);
            const date = new Date(y, m - 1, d);
            return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
        }
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
    } catch {
        return dateStr;
    }
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
                    className={`flex h-12 w-12 items-center justify-center rounded-xl ${tones[tone] || tones.blue}`}
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
    const { can, hasRole } = usePermission();
    const queryClient = useQueryClient();

    // Permission guard
    useEffect(() => {
        if (!hasRole("administrator") && !can("departments.manage")) {
            notify.error(
                "Access Denied",
                "You do not have permission to access archived departments."
            );
            router.visit("/departments");
        }
    }, [can, hasRole]);

    const [search, setSearch] = useState("");
    const [restoreTarget, setRestoreTarget] = useState(null);

    // Fetch Archived Departments
    const {
        data: archivedDepartments = [],
        isLoading: loading,
        isError,
        error,
    } = useQuery({
        queryKey: archivedDepartmentsQueryKey,
        enabled: Boolean(getAuthToken()),
        refetchOnMount: "always",
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get("/v1/departments/archives", {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return getCollection(response);
        },
    });

    // Restore Mutation
    const restoreMutation = useMutation({
        mutationFn: async (departmentId) => {
            const token = getAuthToken();
            const response = await api.post(
                `/v1/departments/${departmentId}/restore`,
                {},
                {
                    headers: token ? { Authorization: `Bearer ${token}` } : {},
                }
            );
            return response.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: archivedDepartmentsQueryKey });
            queryClient.invalidateQueries({ queryKey: departmentsQueryKey });

            notify.success(
                "Department Restored",
                data?.message || "The department has been restored successfully."
            );
            setRestoreTarget(null);
        },
        onError: (err) => {
            const message =
                err.response?.data?.message ||
                "Failed to restore department. Please try again.";
            notify.error("Restore Failed", message);
        },
    });

    const counts = useMemo(() => {
        const total = archivedDepartments.length;
        const totalPrograms = archivedDepartments.reduce(
            (acc, d) => acc + (d.programs_count ?? (d.programs?.length || 0)),
            0
        );

        return { total, totalPrograms };
    }, [archivedDepartments]);

    const filteredDepartments = useMemo(() => {
        const needle = search.trim().toLowerCase();
        if (!needle) return archivedDepartments;

        return archivedDepartments.filter((dept) => {
            const codeLower = (dept.department_code || "").toLowerCase();
            const nameLower = (dept.department_name || "").toLowerCase();
            const descLower = (dept.description || "").toLowerCase();

            return (
                codeLower.includes(needle) ||
                nameLower.includes(needle) ||
                descLower.includes(needle)
            );
        });
    }, [archivedDepartments, search]);

    const columns = [
        {
            key: "department_code",
            header: "Code",
            width: "120px",
            render: (dept) => (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {dept.department_code}
                </span>
            ),
        },
        {
            key: "department_name",
            header: "Department Name",
            render: (dept) => (
                <div>
                    <p className="font-semibold text-gray-900">
                        {dept.department_name}
                    </p>
                    {dept.description && (
                        <p className="max-w-md truncate text-xs text-gray-400 mt-0.5">
                            {dept.description}
                        </p>
                    )}
                </div>
            ),
        },
        {
            key: "programs",
            header: "Programs",
            render: (dept) => {
                const count = dept.programs_count ?? (dept.programs?.length || 0);
                return (
                    <span className="text-xs text-gray-600 font-medium">
                        {count} {count === 1 ? "Program" : "Programs"}
                    </span>
                );
            },
        },
        {
            key: "deleted_at",
            header: "Archived Date",
            render: (dept) => (
                <span className="text-xs text-gray-500 whitespace-nowrap">
                    {formatDate(dept.deleted_at)}
                </span>
            ),
        },
        {
            key: "action",
            header: "Action",
            width: "160px",
            render: (dept) => (
                <button
                    type="button"
                    onClick={() => setRestoreTarget(dept)}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]"
                >
                    <ArrowPathIcon className="h-4 w-4" />
                    Restore Department
                </button>
            ),
        },
    ];

    const sortOptions = [
        { label: "Default", value: "default" },
        {
            label: "Code A-Z",
            value: "code_asc",
            sorter: (a, b) => (a.department_code || "").localeCompare(b.department_code || ""),
        },
        {
            label: "Name A-Z",
            value: "name_asc",
            sorter: (a, b) => (a.department_name || "").localeCompare(b.department_name || ""),
        },
    ];

    return (
        <>
            <Head title="Archived Departments" />

            <div className="space-y-6">
                {/* Header with Breadcrumbs & Action Links */}
                <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Departments", href: "/departments" },
                                { label: "Archived Departments" },
                            ]}
                        />
                    </div>

                    <button
                        type="button"
                        onClick={() => router.visit("/departments")}
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Departments
                    </button>
                </div>

                {/* Metric StatCards */}
                <div className="grid gap-4 sm:grid-cols-2">
                    <StatCard
                        icon={ArchiveBoxIcon}
                        label="Total Archived Departments"
                        value={counts.total}
                        tone="gray"
                    />
                    <StatCard
                        icon={AcademicCapIcon}
                        label="Programs Affected"
                        value={counts.totalPrograms}
                        tone="blue"
                    />
                </div>

                {/* Filter & Search Bar */}
                <section className="rounded-xl bg-white p-4 shadow-sm shadow-blue-950/5">
                    <div className="relative">
                        <MagnifyingGlassIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search archived departments..."
                            className="h-11 w-full rounded-xl bg-gray-50 pl-11 pr-4 text-sm text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                </section>

                {/* DataTable */}
                <DataTable
                    columns={columns}
                    data={filteredDepartments}
                    loading={loading}
                    rowKey="department_id"
                    sortOptions={sortOptions}
                    defaultSort="default"
                    emptyMessage="No archived departments found."
                />
            </div>

            {/* Restore Confirmation Modal */}
            <Modal
                isOpen={Boolean(restoreTarget)}
                onClose={() => setRestoreTarget(null)}
                title="Restore Department"
                description="Are you sure you want to restore this department back to active status?"
                icon={<ArrowPathIcon className="h-6 w-6 text-blue-600" />}
                iconBg="bg-blue-50 text-blue-600"
                maxWidth="md"
            >
                {restoreTarget && (
                    <div className="space-y-4 pt-2">
                        <div className="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                            <p className="text-sm font-bold text-blue-900">
                                {restoreTarget.department_name} ({restoreTarget.department_code})
                            </p>
                            {restoreTarget.programs?.length > 0 && (
                                <p className="mt-1 text-xs text-blue-700">
                                    {restoreTarget.programs.length} associated degree programs will also be restored.
                                </p>
                            )}
                        </div>

                        <p className="text-xs text-gray-500">
                            Restoring this department will make it available again in academic management and scheduling.
                        </p>

                        <div className="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <button
                                type="button"
                                onClick={() => setRestoreTarget(null)}
                                disabled={restoreMutation.isPending}
                                className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={() => restoreMutation.mutate(restoreTarget.department_id)}
                                disabled={restoreMutation.isPending}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50"
                            >
                                {restoreMutation.isPending && (
                                    <ArrowPathIcon className="h-4 w-4 animate-spin" />
                                )}
                                Restore Department
                            </button>
                        </div>
                    </div>
                )}
            </Modal>
        </>
    );
}

Archives.layout = (page) => <MainLayout>{page}</MainLayout>;
