export default function SummaryTile({ label, value, icon: Icon }) {
    return (
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
                        title={value || "N/A"}
                    >
                        {value || "N/A"}
                    </p>
                </div>
            </div>
        </div>
    );
}
