import {
    Bluetooth,
    ScanFace,
    ShieldCheck,
    BarChart3,
    Clock,
    Wifi,
} from "lucide-react";
import FeatureCard from "@/Components/Landing/FeatureCard";

const FEATURES = [
    {
        icon: Bluetooth,
        title: "BLE Proximity Detection",
        description:
            "The student's registered device detects nearby Bluetooth Low Energy (BLE) beacons to automatically start the attendance process upon entering the classroom.",
    },
    {
        icon: ScanFace,
        title: "Face Recognition",
        description:
            "Real-time facial recognition verifies each student's identity, preventing proxy attendance and ensuring accurate records.",
    },
    {
        icon: ShieldCheck,
        title: "Automatic Attendance Grading",
        description:
            "Attendance grades are calculated automatically based on customizable rules such as attendance percentage, lateness, absences, and class requirements.",
    },
    {
        icon: BarChart3,
        title: "Live Dashboard",
        description:
            "Monitor attendance in real time, filter records by class or date, and generate detailed reports with just a few clicks.",
    },
    {
        icon: Clock,
        title: "Automatic Timestamps",
        description:
            "Time-in and time-out are recorded automatically the moment a student enters or leaves the classroom.",
    },
    {
        icon: Wifi,
        title: "Works Offline",
        description:
            "Attendance records are stored locally when the network is unavailable and automatically synchronized once the connection is restored.",
    },
];

export default function FeaturesSection() {
    return (
        <section
            id="features"
            className="py-24 bg-linear-to-br from-blue-500 via-blue-600 to-blue-700 dark:from-blue-900 dark:to-gray-950"
        >
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                {/* Header */}
                <div className="text-center mb-14">
                    <p className="text-sm font-semibold text-blue-200 uppercase tracking-wider mb-3">
                        Classroom Attendance System
                    </p>

                    <h2 className="text-3xl sm:text-4xl font-bold text-white">
                        Built for Modern Classroom Attendance
                    </h2>

                    <p className="mt-4 text-blue-100 max-w-2xl mx-auto">
                        Designed specifically for schools, PresenSure automates
                        classroom attendance using Bluetooth Low Energy (BLE)
                        technology and facial recognition to provide fast,
                        secure, and accurate attendance tracking.
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
