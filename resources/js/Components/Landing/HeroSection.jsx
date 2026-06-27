import { ArrowRight } from "lucide-react";
import Badge from "@/Components/UI/Badge";
import Button from "@/Components/UI/Button";
import ScanPulse from "@/Components/Landing/ScanPulse";

export default function HeroSection() {
    return (
        <section className="relative min-h-screen flex items-center pt-16 overflow-hidden bg-white dark:bg-gray-950">
            {/* Background grid */}
            {/* Background grid - Light */}
            <div
                className="absolute inset-0 dark:hidden"
                style={{
                    backgroundImage: `
            linear-gradient(to right, rgba(229,231,235,0.35) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(229,231,235,0.35) 1px, transparent 1px)
        `,
                    backgroundSize: "48px 48px",
                }}
            />

            {/* Background grid - Dark */}
            <div
                className="absolute inset-0 hidden dark:block"
                style={{
                    backgroundImage: `
            linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px)
        `,
                    backgroundSize: "48px 48px",
                }}
            />

            {/* Glow blob */}
            <div className="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-150 h-150 bg-blue-100 dark:bg-blue-900/20 rounded-full blur-3xl opacity-40 pointer-events-none" />

            <div className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div className="grid lg:grid-cols-2 gap-16 items-center">
                    {/* Copy */}
                    <div className="flex flex-col gap-6">
                        <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-[1.1]">
                            Attendance that{" "}
                            <span className="text-blue-600 dark:text-blue-400">
                                recognizes
                            </span>{" "}
                            you.
                        </h1>

                        <p className="text-lg text-gray-500 dark:text-gray-400 leading-relaxed max-w-lg">
                            PresenSure combines Bluetooth proximity detection
                            with real-time face recognition to log attendance
                            automatically.
                        </p>

                        <div className="flex flex-wrap gap-3 pt-2">
                            <a href="#how-it-works">
                                <Button size="lg" variant="outline">
                                    See How It Works
                                </Button>
                            </a>
                        </div>
                    </div>

                    {/* Signature visual */}
                    <div className="flex items-center justify-center">
                        <ScanPulse className="w-64 h-64" />
                    </div>
                </div>
            </div>
        </section>
    );
}
