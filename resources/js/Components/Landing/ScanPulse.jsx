import Logo from "@/assets/images/whiteLogo.webp";

export default function ScanPulse({ className = "" }) {
    return (
        <div
            className={`relative flex items-center justify-center ${className}`}
        >
            {/* Outer pulse rings */}
            <span className="absolute inset-0 rounded-full bg-blue-400/20 dark:bg-blue-400/10 animate-ping [animation-duration:2s]" />
            <span className="absolute inset-4 rounded-full bg-blue-400/20 dark:bg-blue-400/10 animate-ping [animation-duration:2s] [animation-delay:0.4s]" />

            {/* Core circle */}
            <div className="relative z-10 w-48 h-48 rounded-full bg-linear-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-900 shadow-2xl shadow-blue-500/40 flex items-center justify-center">
                {/* Face scan SVG */}
                <img
                    src={Logo}
                    alt="PresenSure Logo"
                    className="w-35 h-35 object-contain"
                />
            </div>
        </div>
    );
}
