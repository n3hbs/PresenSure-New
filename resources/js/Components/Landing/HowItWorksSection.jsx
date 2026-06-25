const STEPS = [
    {
        number: '01',
        title: 'BLE Beacon detects your device',
        description:
            'The moment you enter the room, a Bluetooth beacon picks up your registered device and initiates the session.',
    },
    {
        number: '02',
        title: 'Camera verifies your face',
        description:
            'A brief face scan confirms its really you — taking less than a second without any extra action on your part.',
    },
    {
        number: '03',
        title: 'Attendance is logged instantly',
        description:
            'A timestamped record is written to the system and visible on the admin dashboard in real time.',
    },
];

export default function HowItWorksSection() {
    return (
        <section id="how-it-works" className="py-24 bg-white dark:bg-gray-950">
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="text-center mb-16">
                    <p className="text-sm font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-3">
                        The Process
                    </p>
                    <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        Three seconds. Zero effort.
                    </h2>
                </div>

                {/* Steps */}
                <div className="relative grid md:grid-cols-3 gap-8">
                    {/* Connector line (desktop) */}
                    <div className="hidden md:block absolute top-8 left-[calc(16.67%+1rem)] right-[calc(16.67%+1rem)] h-px bg-gradient-to-r from-blue-200 via-blue-400 to-blue-200 dark:from-blue-900 dark:via-blue-600 dark:to-blue-900" />

                    {STEPS.map((step) => (
                        <div key={step.number} className="relative flex flex-col items-center text-center gap-4">
                            {/* Number bubble */}
                            <div className="relative z-10 w-16 h-16 rounded-full bg-blue-600 dark:bg-blue-500 text-white flex items-center justify-center text-lg font-bold shadow-lg shadow-blue-200 dark:shadow-blue-900">
                                {step.number}
                            </div>

                            <h3 className="text-base font-semibold text-gray-900 dark:text-white">
                                {step.title}
                            </h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs">
                                {step.description}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}