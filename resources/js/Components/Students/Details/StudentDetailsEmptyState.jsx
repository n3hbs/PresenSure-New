import { UserCircleIcon } from "@heroicons/react/24/outline";

export default function StudentDetailsEmptyState() {
    return (
        <section className="rounded-xl bg-white p-8 text-center shadow-sm shadow-blue-950/5">
            <UserCircleIcon className="mx-auto h-12 w-12 text-gray-300" />
            <p className="mt-3 text-sm font-semibold text-gray-700">
                Student not found.
            </p>
        </section>
    );
}
