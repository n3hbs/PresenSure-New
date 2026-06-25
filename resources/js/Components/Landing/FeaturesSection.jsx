import { Bluetooth, ScanFace, ShieldCheck, BarChart3, Clock, Wifi } from 'lucide-react';
import FeatureCard from '@/Components/Landing/FeatureCard';

const FEATURES = [
    {
        icon: Bluetooth,
        title: 'BLE Proximity Detection',
        description:
            'Bluetooth Low Energy beacons automatically detect when a person enters the room — no manual check-in needed.',
    },
    {
        icon: ScanFace,
        title: 'Face Recognition',
        description:
            'On-device facial recognition confirms identity in real time, preventing proxy attendance and buddy punching.',
    },
    {
        icon: ShieldCheck,
        title: 'Privacy First',
        description:
            'Biometric data stays on-device and is never uploaded raw. Full GDPR and PDPA compliance out of the box.',
    },
    {
        icon: BarChart3,
        title: 'Live Dashboard',
        description:
            'See who s present right now. Filter by class, department, or date and export reports in one click.',
    },
    {
        icon: Clock,
        title: 'Automatic Timestamps',
        description:
            'Time-in and time-out are captured the moment you walk in and when you leave — accurate to the second.',
    },
    {
        icon: Wifi,
        title: 'Works Offline',
        description:
            'Logs are cached locally when the connection drops and synced automatically when you re back online.',
    },
];

export default function FeaturesSection() {
    return (
        <section id="features" className="py-24 bg-gradient-to-br from-blue-600 to-blue-800 dark:from-blue-800 dark:to-gray-900">
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="text-center mb-14">
                    <p className="text-sm font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-3">
                        What It Does
                    </p>
                    <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        Everything you need, nothing you don't.
                    </h2>
                    <p className="mt-4 text-gray-500 dark:text-gray-400 max-w-xl mx-auto">
                        Purpose-built for schools, offices, and events where accurate,
                        hands-free attendance matters.
                    </p>
                </div>

                {/* Grid */}
                <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {FEATURES.map((feature) => (
                        <FeatureCard key={feature.title} {...feature} />
                    ))}
                </div>
            </div>
        </section>
    );
}