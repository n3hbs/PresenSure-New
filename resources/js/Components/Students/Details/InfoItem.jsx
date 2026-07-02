export default function InfoItem({ label, value }) {
    return (
        <div className="rounded-lg bg-gray-50 px-4 py-3">
            <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                {label}
            </p>
            <p className="mt-1 break-words text-sm font-semibold text-gray-900">
                {value || "N/A"}
            </p>
        </div>
    );
}
