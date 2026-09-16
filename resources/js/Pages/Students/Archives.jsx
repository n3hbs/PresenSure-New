import { useEffect, useMemo, useState } from "react";
import { Head } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    ArchiveBoxIcon,
    ArrowPathIcon,
    MagnifyingGlassIcon,
    UsersIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DataTable from "@/Components/UI/DataTable";
import Modal from "@/Components/UI/Modal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import {
    activeStudentsQueryKey,
    archivedStudentsQueryKey,
} from "@/Services/queryKeys";
import { notify } from "@/Services/toast";
import NoImage from "@/assets/images/noImage.webp";

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const normalizeStudent = (record) => {
    const user = record.user || record || {};
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
        image: profile.imagelink || profile.image_link || "",
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
    const [search, setSearch] = useState("");
    const [restoreTarget, setRestoreTarget] = useState(null);
    const [restoring, setRestoring] = useState(false);
    const queryClient = useQueryClient();

    const {
        data: archivedStudents = [],
        isLoading: loading,
        isError,
        error,
    } = useQuery({
        queryKey: archivedStudentsQueryKey,
        enabled: Boolean(getAuthToken()),
        refetchOnMount: "always",
        queryFn: async () => {
            const token = sessionStorage.getItem("token");
            const response = await api.get("/student/archives", {
                headers: token
                    ? {
                          Authorization: `Bearer ${token}`,
                      }
                    : {},
            });

            return getCollection(response).map(normalizeStudent);
        },
    });

    const isUnauthenticated = error?.response?.status === 401;
    const errorMessage = isUnauthenticated
        ? ""
        : error?.response?.data?.message || "Unable to load archived students right now.";

    useEffect(() => {
        if (isError && !isUnauthenticated && errorMessage) {
            notify.error("Unable to Load Archives", errorMessage);
        }
    }, [isError, isUnauthenticated, errorMessage]);

    const counts = useMemo(() => {
        const male = archivedStudents.filter(
            (s) => String(s.sex).toLowerCase() === "male",
        ).length;
        const female = archivedStudents.filter(
            (s) => String(s.sex).toLowerCase() === "female",
        ).length;

        return {
            total: archivedStudents.length,
            male,
            female,
        };
    }, [archivedStudents]);

    const filteredStudents = useMemo(() => {
        const needle = search.trim().toLowerCase();
        if (!needle) return archivedStudents;

        return archivedStudents.filter((student) => {
            return [student.userId, student.fullName]
                .join(" ")
                .toLowerCase()
                .includes(needle);
        });
    }, [archivedStudents, search]);

    const handleRestore = async () => {
        if (!restoreTarget?.userId) return;
        setRestoring(true);
        try {
            const token = sessionStorage.getItem("token");
            await api.post(
                `/student/${restoreTarget.userId}/restore`,
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
                "Student Restored",
                `${restoreTarget.fullName} has been restored to active students.`,
            );

            queryClient.invalidateQueries({
                queryKey: archivedStudentsQueryKey,
            });
            queryClient.invalidateQueries({
                queryKey: activeStudentsQueryKey,
            });
            setRestoreTarget(null);
        } catch (err) {
            const message =
                err.response?.data?.message ||
                "Failed to restore student. Please try again.";
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
            render: (student) => (
                <div className="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                    {student.image ? (
                        <img
                            src={student.image}
                            alt={student.fullName}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <img
                            src={NoImage}
                            alt={student.fullName}
                            className="h-full w-full object-cover"
                        />
                    )}
                </div>
            ),
        },
        {
            key: "userId",
            header: "Student ID",
            minWidth: "180px",
            render: (student) => (
                <span className="font-semibold text-gray-900">
                    {student.userId}
                </span>
            ),
        },
        {
            key: "fullName",
            header: "Full Name",
            minWidth: "260px",
            render: (student) => (
                <span className="font-semibold text-gray-900">
                    {student.fullName}
                </span>
            ),
        },
        {
            key: "action",
            header: "Action",
            width: "160px",
            render: (student) => (
                <button
                    type="button"
                    onClick={() => setRestoreTarget(student)}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.98]"
                >
                    <ArrowPathIcon className="h-4 w-4" />
                    Restore Student
                </button>
            ),
        },
    ];

    const sortOptions = [
        {
            label: "Default",
            value: "default",
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
            label: "Student ID",
            value: "id_asc",
            sorter: (a, b) => a.userId.localeCompare(b.userId),
        },
    ];

    return (
        <>
            <Head title="Archived Students" />
            <div className="space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Students", href: "/students" },
                                { label: "Archived Students" },
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
                        icon={UsersIcon}
                        label="Male"
                        value={counts.male}
                        tone="blue"
                    />
                    <StatCard
                        icon={UsersIcon}
                        label="Female"
                        value={counts.female}
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
                                placeholder="Search archived students by ID or name..."
                                className="h-11 w-full rounded-xl bg-gray-50 pl-11 pr-4 text-sm text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            />
                        </div>
                    </div>
                </section>

                <DataTable
                    columns={columns}
                    data={filteredStudents}
                    loading={loading}
                    rowKey="userId"
                    sortOptions={sortOptions}
                    defaultSort="default"
                    pageSizeOptions={[10, 25, 50]}
                    emptyMessage="No archived students found."
                />

                <Modal
                    isOpen={Boolean(restoreTarget)}
                    onClose={() => {
                        if (!restoring) setRestoreTarget(null);
                    }}
                    title="Restore Student"
                    description="Are you sure you want to restore this student to active status?"
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
                                        Restore Student
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
                        return them to the active student roster for the semester.
                    </p>
                </Modal>
            </div>
        </>
    );
}

Archives.layout = (page) => <MainLayout>{page}</MainLayout>;
