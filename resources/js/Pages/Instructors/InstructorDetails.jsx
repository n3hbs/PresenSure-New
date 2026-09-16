import { useEffect, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    ArchiveBoxIcon,
    ArrowPathIcon,
    PencilSquareIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import InstructorDetailsContent from "@/Components/Instructors/Details/InstructorDetailsContent";
import InstructorDetailsSkeleton from "@/Components/Instructors/Details/InstructorDetailsSkeleton";
import ArchiveInstructorModal from "@/Components/Instructors/Details/ArchiveInstructorModal";
import RestoreInstructorModal from "@/Components/Instructors/Details/RestoreInstructorModal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import {
    archivedInstructorsQueryKey,
    instructorsQueryKey,
} from "@/Services/queryKeys";
import { notify } from "@/Services/toast";

export default function InstructorDetails() {
    const queryClient = useQueryClient();
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("user_id");

    const [isArchiveModalOpen, setIsArchiveModalOpen] = useState(false);
    const [isRestoreModalOpen, setIsRestoreModalOpen] = useState(false);

    const {
        data,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["instructor-details", userId],
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get(`instructor/${userId}`, {
                headers: token
                    ? {
                          Authorization: `Bearer ${token}`,
                      }
                    : {},
            });

            return response.data.data;
        },
        enabled: Boolean(userId) && Boolean(getAuthToken()),
    });

    const user = data?.user || {};
    const instructor = data?.instructor || {};
    const role = data?.role || {};
    const profile = data?.profile || {};
    const courses = data?.courses || [];
    const isUnauthenticated = error?.response?.status === 401;
    const errorMessage = isUnauthenticated
        ? ""
        : error?.response?.data?.message || "Unable to load instructor details.";

    useEffect(() => {
        if (!userId) {
            notify.warning(
                "Missing Instructor ID",
                "Please open an instructor from the instructors list."
            );
        }
    }, [userId]);

    useEffect(() => {
        if (isError && !isUnauthenticated && errorMessage) {
            notify.error("Unable to Load Instructor", errorMessage);
        }
    }, [isError, isUnauthenticated, errorMessage]);

    return (
        <div className="space-y-6">
            <Head title="Instructor Details" />
            <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Instructors", href: "/instructors" },
                            { label: userId || "Instructor Details" },
                        ]}
                    />
                </div>

                {data && (
                    <div className="flex flex-wrap items-center gap-2">
                        <Link
                            href={`/instructors/edit?user_id=${userId}`}
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]"
                        >
                            <PencilSquareIcon className="h-4 w-4" />
                            <span>Edit Instructor</span>
                        </Link>

                        {instructor.status === "Inactive" ? (
                            <button
                                type="button"
                                onClick={() => setIsRestoreModalOpen(true)}
                                className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]"
                            >
                                <ArrowPathIcon className="h-4 w-4" />
                                <span>Restore Instructor</span>
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => setIsArchiveModalOpen(true)}
                                className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 text-sm font-semibold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 active:scale-[0.98]"
                            >
                                <ArchiveBoxIcon className="h-4 w-4" />
                                <span>Archive Instructor</span>
                            </button>
                        )}
                    </div>
                )}
            </div>

            {isLoading ? (
                <InstructorDetailsSkeleton />
            ) : !data ? (
                <section className="rounded-xl bg-white p-8 text-center shadow-sm shadow-blue-950/5">
                    <UserCircleIcon className="mx-auto h-12 w-12 text-gray-300" />
                    <p className="mt-3 text-sm font-semibold text-gray-700">
                        Instructor not found.
                    </p>
                </section>
            ) : (
                <InstructorDetailsContent
                    user={user}
                    instructor={instructor}
                    role={role}
                    profile={profile}
                    courses={courses}
                />
            )}

            {/* Archive Instructor Modal */}
            <ArchiveInstructorModal
                isOpen={isArchiveModalOpen}
                onClose={() => setIsArchiveModalOpen(false)}
                user={user}
                onSuccess={() => {
                    queryClient.invalidateQueries({
                        queryKey: ["instructor-details", userId],
                    });
                    queryClient.invalidateQueries({
                        queryKey: instructorsQueryKey,
                    });
                    queryClient.invalidateQueries({
                        queryKey: archivedInstructorsQueryKey,
                    });
                    router.visit("/instructors");
                }}
            />

            {/* Restore Instructor Modal */}
            <RestoreInstructorModal
                isOpen={isRestoreModalOpen}
                onClose={() => setIsRestoreModalOpen(false)}
                user={user}
                onSuccess={() => {
                    queryClient.invalidateQueries({
                        queryKey: ["instructor-details", userId],
                    });
                    queryClient.invalidateQueries({
                        queryKey: instructorsQueryKey,
                    });
                    queryClient.invalidateQueries({
                        queryKey: archivedInstructorsQueryKey,
                    });
                }}
            />
        </div>
    );
}

InstructorDetails.layout = (page) => <MainLayout>{page}</MainLayout>;
