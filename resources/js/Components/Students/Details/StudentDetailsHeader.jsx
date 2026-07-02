import {
    AcademicCapIcon,
    BookOpenIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";

import NoImage from "@/assets/images/noImage.webp";

import SummaryTile from "./SummaryTile";
import {
    fieldFallback,
    formatDisplayName,
    formatStudentName,
} from "./studentDetailsFormatters";

const MetaBadge = ({ children, icon: Icon, tone = "blue" }) => {
    const tones = {
        blue: "border-blue-100 bg-blue-50 text-blue-700",
        green: "border-green-100 bg-green-50 text-green-700",
        gray: "border-gray-200 bg-gray-50 text-gray-600",
    };

    return (
        <span
            className={`inline-flex min-h-9 items-center gap-2 rounded-lg border px-3 text-sm font-semibold ${tones[tone]}`}
        >
            {Icon && <Icon className="h-4 w-4 shrink-0" />}
            {children}
        </span>
    );
};

export default function StudentDetailsHeader({
    user = {},
    student = {},
    role = {},
    profile = {},
}) {
    const program = student.program || {};
    const profileImage = profile.imagelink || NoImage;

    return (
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
                        <h1 className="mt-1 break-words text-2xl font-bold text-gray-900">
                            {formatStudentName(user)}
                        </h1>
                        <div className="mt-3 flex flex-wrap justify-center gap-2 md:justify-start">
                            {role.role_name && (
                                <MetaBadge tone="gray">
                                    {role.role_name}
                                </MetaBadge>
                            )}
                            <MetaBadge tone="blue">
                                {user.sex || fieldFallback}
                            </MetaBadge>
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
                                program.program_code || program.program_name
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
    );
}
