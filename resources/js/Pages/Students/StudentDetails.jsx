import { Link } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeftIcon, UserCircleIcon } from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import StudentDetailsContent from "@/Components/Students/Details/StudentDetailsContent";
import StudentDetailsSkeleton from "@/Components/Students/Details/StudentDetailsSkeleton";
import api from "@/Services/api";

export default function StudentDetails() {
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("user_id");

    const {
        data,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["student-details", userId],
        queryFn: async () => {
            const token = sessionStorage.getItem("token");
            const response = await api.get(`student/${userId}`, {
                headers: token
                    ? {
                          Authorization: `Bearer ${token}`,
                      }
                    : {},
            });

            return response.data.data;
        },
        enabled: Boolean(userId),
    });

    const user = data?.user || {};
    const student = data?.student?.[0] || {};
    const role = data?.role || {};
    const profile = data?.profile || {};
    const courses = data?.courses || [];
    const errorMessage =
        error?.response?.data?.message || "Unable to load student details.";

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <Breadcrumbs
                    crumbs={[
                        { label: "Dashboard", href: "/dashboard" },
                        { label: "Students", href: "/students" },
                        { label: "Student Details" },
                    ]}
                />

                <Link href="/students">
                    <Button type="button" variant="outline" size="sm">
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Students
                    </Button>
                </Link>
            </div>

            {!userId && (
                <div className="rounded-lg bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
                    Missing student ID. Please open a student from the students
                    list.
                </div>
            )}

            {isError && (
                <div className="rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {errorMessage}
                </div>
            )}

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
        </div>
    );
}

StudentDetails.layout = (page) => <MainLayout>{page}</MainLayout>;
