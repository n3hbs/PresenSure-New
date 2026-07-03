import {
    AcademicCapIcon,
    BookOpenIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";

import NoImage from "@/assets/images/noImage.webp";

const fieldFallback = "N/A";

const formatStudentName = (user = {}) =>
    [
        user.last_name,
        user.first_name,
        user.suffix,
        user.middle_initial ? `${user.middle_initial}.` : "",
    ]
        .filter(Boolean)
        .join(" ") || fieldFallback;

const formatDisplayName = (user = {}) =>
    [user.first_name, user.middle_initial, user.last_name, user.suffix]
        .filter(Boolean)
        .join(" ") || fieldFallback;

const MetaBadge = ({ children, tone = "blue" }) => {
    const tones = {
        blue: "border-blue-100 bg-blue-50 text-blue-700",
        green: "border-green-100 bg-green-50 text-green-700",
        gray: "border-gray-200 bg-gray-50 text-gray-600",
    };

    return (
        <span
            className={`inline-flex min-h-9 items-center rounded-lg border px-3 text-sm font-semibold ${tones[tone]}`}
        >
            {children}
        </span>
    );
};

const SummaryTile = ({ label, value, icon: Icon }) => (
    <div className="rounded-lg border border-blue-100 bg-blue-50 p-4">
        <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-700 shadow-sm shadow-blue-950/5">
                <Icon className="h-5 w-5" />
            </div>
            <div className="min-w-0">
                <p className="text-xs font-semibold uppercase tracking-wide text-blue-600">
                    {label}
                </p>
                <p
                    className="mt-1 truncate text-sm font-bold text-gray-900"
                    title={value || fieldFallback}
                >
                    {value || fieldFallback}
                </p>
            </div>
        </div>
    </div>
);

const InfoItem = ({ label, value }) => (
    <div className="rounded-lg bg-gray-50 px-4 py-3">
        <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">
            {label}
        </p>
        <p className="mt-1 wrap-break-words text-sm font-semibold text-gray-900">
            {value || fieldFallback}
        </p>
    </div>
);

export default function StudentDetailsContent({
    user = {},
    student = {},
    role = {},
    profile = {},
    courses = [],
}) {
    const program = student.program || {};
    const department = program.department || {};
    const profileImage = profile.imagelink || NoImage;

    return (
        <>
            <section className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
                <div className="flex flex-col gap-6 md:flex-row md:items-start">
                    <div className="mx-auto flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 ring-4 ring-blue-50 md:mx-0">
                        <img
                            src={profileImage}
                            alt={formatDisplayName(user)}
                            className="h-full w-full object-cover"
                        />
                    </div>

                    <div className="min-w-0 flex-1">
                        <div className="text-center md:text-left">
                            <p className="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Student Profile
                            </p>
                            <h1 className="mt-1 wrap-break-words text-2xl font-bold text-gray-900">
                                {formatStudentName(user)}
                            </h1>
                            <div className="mt-3 flex flex-wrap justify-center gap-2 md:justify-start">
                                {role.role_name && (
                                    <MetaBadge tone="gray">
                                        {role.role_name}
                                    </MetaBadge>
                                )}
                                <MetaBadge tone="green">
                                    {student.status || fieldFallback}
                                </MetaBadge>
                            </div>
                        </div>

                        <div className="mt-6 grid gap-4 sm:grid-cols-3">
                            <SummaryTile
                                icon={AcademicCapIcon}
                                label="Program"
                                value={
                                    program.program_code ||
                                    program.program_name
                                }
                            />
                            <SummaryTile
                                icon={BookOpenIcon}
                                label="Year Level"
                                value={student.year}
                            />
                            <SummaryTile
                                icon={UserCircleIcon}
                                label="Block"
                                value={student.block}
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
                <h2 className="text-lg font-semibold text-gray-900">
                    Student Information
                </h2>

                <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <InfoItem label="Student ID" value={user.user_id} />
                    <InfoItem label="Sex" value={user.sex} />
                    <InfoItem
                        label="Program Name"
                        value={program.program_name}
                    />
                    <InfoItem
                        label="Department"
                        value={department.department_name}
                    />
                </div>
            </section>

            <section className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <BookOpenIcon className="h-5 w-5" />
                    </div>
                    <h2 className="text-lg font-semibold text-gray-900">
                        Enrolled Courses
                    </h2>
                </div>

                {courses.length > 0 ? (
                    <div className="mt-5 grid gap-4">
                        {courses.map((course) => (
                            <div
                                key={course.course_id}
                                className="rounded-lg border border-gray-200 bg-white p-4"
                            >
                                <p className="font-semibold text-gray-900">
                                    {course.subject_code || "Course"}
                                </p>
                                <p className="mt-1 text-sm text-gray-500">
                                    {course.description || "No description"}
                                </p>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="mt-5 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-10 text-center">
                        <p className="text-sm font-medium text-gray-500">
                            No enrolled course details are available for this
                            student yet.
                        </p>
                    </div>
                )}
            </section>
        </>
    );
}
