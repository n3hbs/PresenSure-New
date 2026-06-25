/**
 * ScanPulse — animated SVG combining BLE radio rings with a face-scan overlay.
 * Signature visual element of the landing page hero.
 */
export default function ScanPulse({ className = "" }) {
    return (
        <div
            className={`relative flex items-center justify-center ${className}`}
        >
            {/* Outer pulse rings */}
            <span className="absolute inset-0 rounded-full bg-blue-400/20 dark:bg-blue-400/10 animate-ping [animation-duration:2s]" />
            <span className="absolute inset-4 rounded-full bg-blue-400/20 dark:bg-blue-400/10 animate-ping [animation-duration:2s] [animation-delay:0.4s]" />

            {/* Core circle */}
            <div className="relative z-10 w-48 h-48 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-900 shadow-2xl shadow-blue-500/40 flex items-center justify-center">
                {/* Face scan SVG */}
                <svg
                    viewBox="0 0 100 100"
                    className="w-28 h-28"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    {/* Face outline */}
                    <ellipse
                        cx="50"
                        cy="45"
                        rx="22"
                        ry="26"
                        stroke="white"
                        strokeWidth="2"
                        strokeOpacity="0.9"
                    />

                    {/* Eyes */}
                    <circle
                        cx="42"
                        cy="40"
                        r="2.5"
                        fill="white"
                        fillOpacity="0.9"
                    />
                    <circle
                        cx="58"
                        cy="40"
                        r="2.5"
                        fill="white"
                        fillOpacity="0.9"
                    />

                    {/* Mouth */}
                    <path
                        d="M43 52 Q50 57 57 52"
                        stroke="white"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeOpacity="0.9"
                    />

                    {/* Scan line — animates top-to-bottom */}
                    <line
                        x1="28"
                        y1="50"
                        x2="72"
                        y2="50"
                        stroke="#93C5FD"
                        strokeWidth="1.5"
                        strokeOpacity="0.8"
                    >
                        <animateTransform
                            attributeName="transform"
                            type="translate"
                            values="0 -18; 0 18; 0 -18"
                            dur="2.5s"
                            repeatCount="indefinite"
                        />
                    </line>

                    {/* Corner brackets — face recognition corners */}
                    {/* Top-left */}
                    <path
                        d="M20 30 L20 22 L28 22"
                        stroke="white"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                        strokeOpacity="0.7"
                    />
                    {/* Top-right */}
                    <path
                        d="M80 30 L80 22 L72 22"
                        stroke="white"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                        strokeOpacity="0.7"
                    />
                    {/* Bottom-left */}
                    <path
                        d="M20 72 L20 80 L28 80"
                        stroke="white"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                        strokeOpacity="0.7"
                    />
                    {/* Bottom-right */}
                    <path
                        d="M80 72 L80 80 L72 80"
                        stroke="white"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                        strokeOpacity="0.7"
                    />

                    {/* BLE signal dots at bottom */}
                    <circle
                        cx="44"
                        cy="86"
                        r="2"
                        fill="white"
                        fillOpacity="0.5"
                    >
                        <animate
                            attributeName="fill-opacity"
                            values="0.3;0.9;0.3"
                            dur="1.2s"
                            repeatCount="indefinite"
                        />
                    </circle>
                    <circle
                        cx="50"
                        cy="86"
                        r="2"
                        fill="white"
                        fillOpacity="0.7"
                    >
                        <animate
                            attributeName="fill-opacity"
                            values="0.3;0.9;0.3"
                            dur="1.2s"
                            begin="0.2s"
                            repeatCount="indefinite"
                        />
                    </circle>
                    <circle
                        cx="56"
                        cy="86"
                        r="2"
                        fill="white"
                        fillOpacity="0.5"
                    >
                        <animate
                            attributeName="fill-opacity"
                            values="0.3;0.9;0.3"
                            dur="1.2s"
                            begin="0.4s"
                            repeatCount="indefinite"
                        />
                    </circle>
                </svg>
            </div>
        </div>
    );
}
