import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";

import Button from "@/Components/UI/Button";

export default function DiscardRegistrationModal({
    open,
    onKeepEditing,
    onDiscard,
}) {
    if (!open) return null;

    return (
        <div className="fixed inset-0 z-90 flex items-center justify-center bg-gray-950/50 p-4">
            <div
                className="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl shadow-blue-950/20"
                role="dialog"
                aria-modal="true"
                aria-labelledby="discard-registration-title"
            >
                <div className="flex items-start gap-3">
                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <ExclamationTriangleIcon className="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            id="discard-registration-title"
                            className="text-lg font-bold text-gray-900"
                        >
                            Discard registration?
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            You have filled up inputs already. Leaving this page
                            will clear the registration form.
                        </p>
                    </div>
                </div>

                <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" onClick={onDiscard}>
                        Discard
                    </Button>
                    <Button type="button" onClick={onKeepEditing}>
                        Keep Editing
                    </Button>
                </div>
            </div>
        </div>
    );
}
