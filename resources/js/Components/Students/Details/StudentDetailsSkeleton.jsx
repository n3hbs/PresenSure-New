export default function StudentDetailsSkeleton() {
    return (
        <div className="space-y-6">
            <div className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
                <div className="flex animate-pulse flex-col gap-6 md:flex-row">
                    <div className="h-32 w-32 rounded-full bg-gray-100" />
                    <div className="flex-1 space-y-4">
                        <div className="h-7 w-64 rounded bg-gray-100" />
                        <div className="h-4 w-40 rounded bg-gray-100" />
                        <div className="grid gap-4 sm:grid-cols-3">
                            <div className="h-20 rounded-lg bg-gray-100" />
                            <div className="h-20 rounded-lg bg-gray-100" />
                            <div className="h-20 rounded-lg bg-gray-100" />
                        </div>
                    </div>
                </div>
            </div>
            <div className="h-72 animate-pulse rounded-xl bg-white shadow-sm shadow-blue-950/5" />
        </div>
    );
}
