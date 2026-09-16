import { useEffect, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    ArchiveBoxIcon,
    PencilSquareIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import StudentDetailsContent from "@/Components/Students/Details/StudentDetailsContent";
import StudentDetailsSkeleton from "@/Components/Students/Details/StudentDetailsSkeleton";
import ArchiveStudentModal from "@/Components/Students/Details/ArchiveStudentModal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import { activeStudentsQueryKey } from "@/Services/queryKeys";
import { notify } from "@/Services/toast";

export default function StudentDetails() {
    const queryClient = useQueryClient();
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("user_id");

    const [isArchiveModalOpen, setIsArchiveModalOpen] = useState(false);

    const {
        data,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["student-details", userId],
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get(`student/${userId}`, {
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
    const student = data?.student?.[0] || {};
    const role = data?.role || {};
    const profile = data?.profile || {};
    const courses = data?.courses || [];
    const isUnauthenticated = error?.response?.status === 401;
    const errorMessage = isUnauthenticated
        ? ""
        : error?.response?.data?.message || "Unable to load student details.";

    useEffect(() => {
        if (!userId) {
            notify.warning(
                "Missing Student ID",
                "Please open a student from the students list."
            );
        }
    }, [userId]);

    useEffect(() => {
        if (isError && !isUnauthenticated && errorMessage) {
            notify.error("Unable to Load Student", errorMessage);
        }
    }, [isError, isUnauthenticated, errorMessage]);

    return (
        <div className="space-y-6">
            <Head title="Student Details" />
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Students", href: "/students" },
                            { label: userId || "Student Details" },
                        ]}
                    />
                </div>

                {data && (
                    <div className="flex flex-wrap items-center gap-2">
                        <Link
                            href={`/students/edit?user_id=${userId}`}
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3.5 text-sm font-semibold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.98]"
                        >
                            <PencilSquareIcon className="h-4 w-4" />
                            <span>Edit Student</span>
                        </Link>

                        <button
                            type="button"
                            onClick={() => setIsArchiveModalOpen(true)}
                            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 text-sm font-semibold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 active:scale-[0.98]"
                        >
                            <ArchiveBoxIcon className="h-4 w-4" />
                            <span>Archive Student</span>
                        </button>
                    </div>
                )}
            </div>


            {isLoading ? (
                <StudentDetailsSkeleton />
            ) : !data ? (
                <section className="rounded-xl bg-white p-8 text-center shadow-sm shadow-blue-950/5">
                    <UserCircleIcon className="mx-auto h-12 w-12 text-gray-300" />
                    <p className="mt-3 text-sm font-semibold text-gray-700">
                        Student not found.
                    </p>
                </section>
            ) : (
                <StudentDetailsContent
                    user={user}
                    student={student}
                    role={role}
                    profile={profile}
                    courses={courses}
                />
            )}

            {/* Archive Student Modal */}
            <ArchiveStudentModal
                isOpen={isArchiveModalOpen}
                onClose={() => setIsArchiveModalOpen(false)}
                user={user}
                student={student}
                onSuccess={() => {
                    queryClient.invalidateQueries({
                        queryKey: ["student-details", userId],
                    });
                    queryClient.invalidateQueries({
                        queryKey: activeStudentsQueryKey,
                    });
                    router.visit("/students");
                }}
            />
        </div>
    );
}

StudentDetails.layout = (page) => <MainLayout>{page}</MainLayout>;
