import InfoItem from "./InfoItem";

export default function StudentInformation({ user = {}, student = {} }) {
    const program = student.program || {};
    const department = program.department || {};

    return (
        <section className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
            <h2 className="text-lg font-semibold text-gray-900">
                Student Information
            </h2>

            <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <InfoItem label="Student ID" value={user.user_id} />
                <InfoItem label="Program Name" value={program.program_name} />
                <InfoItem
                    label="Department"
                    value={department.department_name}
                />
            </div>
        </section>
    );
}
