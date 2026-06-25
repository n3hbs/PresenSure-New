export default function FeatureCard({
    icon: Icon,
    title,
    description,
    className = "",
}) {
    return (
        <div
            className={`
                 flex flex-col items-center justify-center text-center
                px-6 py-8 rounded-2xl
                bg-white/10 dark:bg-white/5
                backdrop-blur-sm
                border border-white/20 dark:border-white/10
                ${className}
            `}
        >
            {/* Icon container */}
            <div className="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-colors duration-300">
                <Icon size={22} />
            </div>

            <div>
                <h3 className="text-base font-semibold text-gray-900 dark:text-white mb-1">
                    {title}
                </h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    {description}
                </p>
            </div>

            {/* Subtle accent line */}
            <div className="absolute bottom-0 left-6 right-6 h-0.5 rounded-full bg-blue-600/0 group-hover:bg-blue-600/20 dark:group-hover:bg-blue-400/30 transition-all duration-300" />
        </div>
    );
}
