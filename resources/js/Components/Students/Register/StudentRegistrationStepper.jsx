const steps = [
    { number: 1, label: "Student Type" },
    { number: 2, label: "Information" },
    { number: 3, label: "Review" },
];

export default function StudentRegistrationStepper({ currentStep }) {
    return (
        <section className="rounded-xl bg-white p-4 shadow-sm shadow-blue-950/5">
            <div className="grid gap-3 sm:grid-cols-3">
                {steps.map((step) => {
                    const active = currentStep === step.number;
                    const complete = currentStep > step.number;

                    return (
                        <div
                            key={step.number}
                            className={`flex items-center gap-3 rounded-lg border px-4 py-3 ${
                                active
                                    ? "border-blue-200 bg-blue-50 text-blue-700"
                                    : complete
                                      ? "border-green-200 bg-green-50 text-green-700"
                                      : "border-gray-200 bg-gray-50 text-gray-500"
                            }`}
                        >
                            <span
                                className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold ${
                                    active
                                        ? "bg-blue-600 text-white"
                                        : complete
                                          ? "bg-green-600 text-white"
                                          : "bg-white text-gray-400"
                                }`}
                            >
                                {step.number}
                            </span>
                            <span className="text-sm font-semibold">
                                {step.label}
                            </span>
                        </div>
                    );
                })}
            </div>
        </section>
    );
}
