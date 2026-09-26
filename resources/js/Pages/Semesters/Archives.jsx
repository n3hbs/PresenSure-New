import { useEffect, useMemo, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
    ArchiveBoxIcon,
    ArrowLeftIcon,
    ArrowPathIcon,
    CalendarDaysIcon,
    ClockIcon,
    MagnifyingGlassIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DataTable from "@/Components/UI/DataTable";
import Modal from "@/Components/UI/Modal";
import SelectDropdown from "@/Components/UI/SelectDropdown";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import {
    semestersQueryKey,
    archivedSemestersQueryKey,
    activeSemesterQueryKey,
    schoolYearsQueryKey,
} from "@/Services/queryKeys";
import { notify } from "@/Services/toast";
import usePermission from "@/Hooks/usePermission";

const allOption = { label: "All", value: "" };

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
        if (!hasRole("administrator") && !can("semesters.manage")) {
            notify.error(
                "Access Denied",
                "You do not have permission to access archived semesters."
            );
            router.visit("/semesters");
        }
    }, [can, hasRole]);

    // Filters
    const [search, setSearch] = useState("");
    const [selectedSchoolYear, setSelectedSchoolYear] = useState("");
    const [selectedTerm, setSelectedTerm] = useState("");
    const [restoreTarget, setRestoreTarget] = useState(null);

    // Fetch Archived Semesters
    const {
        data: archivedSemesters = [],
        isLoading: loading,
        isError,
        error,
    } = useQuery({
        queryKey: archivedSemestersQueryKey,
        enabled: Boolean(getAuthToken()),
        refetchOnMount: "always",
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get("/v1/semesters/archives", {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return getCollection(response);
        },
    });

    // Fetch School Years for filter dropdown
    const { data: schoolYears = [] } = useQuery({
        queryKey: schoolYearsQueryKey,
        enabled: Boolean(getAuthToken()),
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get("/v1/semesters/school-years", {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return getCollection(response);
        },
    });

    // Restore Mutation
    const restoreMutation = useMutation({
        mutationFn: async (semesterId) => {
            const token = getAuthToken();
            const response = await api.post(
                `/v1/semesters/${semesterId}/restore`,
                {},
                {
                    headers: token ? { Authorization: `Bearer ${token}` } : {},
                }
            );
            return response.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: archivedSemestersQueryKey });
            queryClient.invalidateQueries({ queryKey: semestersQueryKey });
            queryClient.invalidateQueries({ queryKey: activeSemesterQueryKey });

            notify.success(
                "Semester Restored",
                data?.message || "The semester has been restored successfully."
            );
            setRestoreTarget(null);
        },
        onError: (err) => {
            const message =
                err.response?.data?.message ||
                "Failed to restore semester. Please try again.";
            notify.error("Restore Failed", message);
        },
    });

    const counts = useMemo(() => {
        return {
            total: archivedSemesters.length,
            firstSem: archivedSemesters.filter((s) => s.term === "First Semester").length,
            secondSem: archivedSemesters.filter((s) => s.term === "Second Semester").length,
            summer: archivedSemesters.filter((s) => s.term === "Summer").length,
        };
    }, [archivedSemesters]);

    const filteredSemesters = useMemo(() => {
        return archivedSemesters.filter((sem) => {
            const termMatch = !selectedTerm || sem.term === selectedTerm;
            const syMatch =
                !selectedSchoolYear ||
                String(sem.school_year_id) === String(selectedSchoolYear) ||
                String(sem.school_year?.school_year_id) === String(selectedSchoolYear);

            const searchLower = search.trim().toLowerCase();
            const searchMatch =
                !searchLower ||
                (sem.term || "").toLowerCase().includes(searchLower) ||
                (sem.remarks || "").toLowerCase().includes(searchLower) ||
                (sem.school_year?.year_range || "").toLowerCase().includes(searchLower);

            return termMatch && syMatch && searchMatch;
        });
    }, [archivedSemesters, search, selectedSchoolYear, selectedTerm]);

    const termOptions = [
        allOption,
        { label: "First Semester", value: "First Semester" },
        { label: "Second Semester", value: "Second Semester" },
        { label: "Summer", value: "Summer" },
    ];

    const schoolYearOptions = useMemo(() => {
        return [
            allOption,
            ...schoolYears.map((sy) => ({
                label: `AY ${sy.year_range}`,
                value: String(sy.school_year_id),
            })),
        ];
    }, [schoolYears]);

    const renderTermBadge = (term) => {
        const styles = {
            "First Semester": "bg-blue-50 text-blue-700 border-blue-200",
            "Second Semester": "bg-indigo-50 text-indigo-700 border-indigo-200",
            "Summer": "bg-amber-50 text-amber-700 border-amber-200",
        };

        return (
            <span
                className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${
                    styles[term] || "bg-gray-100 text-gray-700 border-gray-200"
                }`}
            >
                {term}
            </span>
        );
    };

    const columns = [
        {
            key: "term",
            header: "Academic Term",
            render: (semester) => (
                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-2">
                        {renderTermBadge(semester.term)}
                    </div>
                    <span className="text-xs text-gray-400">
                        AY {semester.school_year?.year_range || "N/A"}
                    </span>
                </div>
            ),
        },
        {
            key: "duration",
            header: "Duration",
            render: (semester) => (
                <span className="text-xs font-semibold text-gray-700 whitespace-nowrap">
                    {formatDate(semester.semester_start)} — {formatDate(semester.semester_end)}
                </span>
            ),
        },
        {
            key: "periods",
            header: "Periods Configured",
            render: (semester) => (
                <span className="text-xs text-gray-500">
                    {semester.periods?.length
                        ? `${semester.periods.length} periods defined`
                        : "No periods"}
                </span>
            ),
        },
        {
            key: "remarks",
            header: "Remarks",
            render: (semester) => (
                <span
                    className="block max-w-xs truncate text-xs text-gray-500"
                    title={semester.remarks || ""}
                >
                    {semester.remarks || "—"}
                </span>
            ),
        },
        {
            key: "action",
            header: "Action",
            width: "160px",
            render: (semester) => (
                <button
                    type="button"
                    onClick={() => setRestoreTarget(semester)}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]"
                >
                    <ArrowPathIcon className="h-4 w-4" />
                    Restore Semester
                </button>
            ),
        },
    ];

    const sortOptions = [
        {
            label: "Default",
            value: "default",
            sorter: () => 0,
        },
        {
            label: "Start Date (Newest)",
            value: "start_desc",
            sorter: (a, b) => (b.semester_start || "").localeCompare(a.semester_start || ""),
        },
        {
            label: "Start Date (Oldest)",
            value: "start_asc",
            sorter: (a, b) => (a.semester_start || "").localeCompare(b.semester_start || ""),
        },
    ];

    return (
        <>
            <Head title="Archived Semesters" />

            <div className="space-y-6">
                {/* Header with Breadcrumbs & Action Links */}
                <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Semesters", href: "/semesters" },
                                { label: "Archived Semesters" },
                            ]}
                        />
                    </div>

                    <button
                        type="button"
                        onClick={() => router.visit("/semesters")}
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Semesters
                    </button>
                </div>

                {/* Metric StatCards */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        icon={ArchiveBoxIcon}
                        label="Total Archived"
                        value={counts.total}
                        tone="gray"
                    />
                    <StatCard
                        icon={CalendarDaysIcon}
                        label="First Semesters"
                        value={counts.firstSem}
                        tone="blue"
                    />
                    <StatCard
                        icon={CalendarDaysIcon}
                        label="Second Semesters"
                        value={counts.secondSem}
                        tone="blue"
                    />
                    <StatCard
                        icon={ClockIcon}
                        label="Summer Terms"
                        value={counts.summer}
                        tone="green"
                    />
                </div>

                {/* Filter & Search Bar */}
                <section className="rounded-xl bg-white p-4 shadow-sm shadow-blue-950/5">
                    <div className="grid gap-3 lg:grid-cols-[1.2fr_repeat(2,minmax(180px,220px))]">
                        <div>
                            <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Search
                            </label>
                            <div className="relative">
                                <MagnifyingGlassIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search archived semesters..."
                                    className="h-11 w-full rounded-xl bg-gray-50 pl-11 pr-4 text-sm text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                                />
                            </div>
                        </div>

                        <SelectDropdown
                            label="School Year"
                            value={selectedSchoolYear}
                            onChange={setSelectedSchoolYear}
                            options={schoolYearOptions}
                            placeholder="All School Years"
                        />

                        <SelectDropdown
                            label="Term"
                            value={selectedTerm}
                            onChange={setSelectedTerm}
                            options={termOptions}
                            placeholder="All Terms"
                        />
                    </div>
                </section>

                {/* DataTable */}
                <DataTable
                    columns={columns}
                    data={filteredSemesters}
                    loading={loading}
                    rowKey="semester_id"
                    sortOptions={sortOptions}
                    defaultSort="default"
                    emptyMessage="No archived semesters found."
                />
            </div>

            {/* Restore Confirmation Modal */}
            <Modal
                isOpen={Boolean(restoreTarget)}
                onClose={() => setRestoreTarget(null)}
                title="Restore Semester"
                description="Are you sure you want to restore this semester back to active status?"
                icon={<ArrowPathIcon className="h-6 w-6 text-blue-600" />}
                iconBg="bg-blue-50 text-blue-600"
                maxWidth="md"
            >
                {restoreTarget && (
                    <div className="space-y-4 pt-2">
                        <div className="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                            <p className="text-sm font-bold text-blue-900">
                                {restoreTarget.term} — A.Y. {restoreTarget.school_year?.year_range || "N/A"}
                            </p>
                            <p className="mt-1 text-xs text-blue-700">
                                Duration: {formatDate(restoreTarget.semester_start)} to {formatDate(restoreTarget.semester_end)}
                            </p>
                            {restoreTarget.periods?.length > 0 && (
                                <p className="mt-1 text-xs text-blue-600">
                                    {restoreTarget.periods.length} associated periods will also be restored.
                                </p>
                            )}
                        </div>

                        <p className="text-xs text-gray-500">
                            Restoring this semester will make it available again in academic management and scheduling.
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
                                onClick={() => restoreMutation.mutate(restoreTarget.semester_id)}
                                disabled={restoreMutation.isPending}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50"
                            >
                                {restoreMutation.isPending && (
                                    <ArrowPathIcon className="h-4 w-4 animate-spin" />
                                )}
                                Restore Semester
                            </button>
                        </div>
                    </div>
                )}
            </Modal>
        </>
    );
}

Archives.layout = (page) => <MainLayout>{page}</MainLayout>;
