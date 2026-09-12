import { useEffect } from "react";
import { XMarkIcon } from "@heroicons/react/24/outline";

export default function Modal({
    isOpen,
    open,
    onClose,
    title,
    description,
    icon,
    iconBg = "bg-blue-50 text-blue-600",
    children,
    footer,
    maxWidth = "md",
    preventClose = false,
    showCloseButton,
    zIndex = "z-90",
}) {
    const isVisible = isOpen ?? open ?? false;

    useEffect(() => {
        if (!isVisible) return;

        const handleKeyDown = (event) => {
            if (event.key === "Escape" && !preventClose && onClose) {
                onClose();
            }
        };

        document.addEventListener("keydown", handleKeyDown);
        return () => document.removeEventListener("keydown", handleKeyDown);
    }, [isVisible, preventClose, onClose]);

    if (!isVisible) return null;

    const maxWidthClasses = {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-lg",
        xl: "max-w-xl",
        "2xl": "max-w-2xl",
    };

    const widthClass = maxWidthClasses[maxWidth] || maxWidthClasses.md;
    const shouldShowClose = showCloseButton ?? !preventClose;

    return (
        <div
            className={`fixed inset-0 ${zIndex} flex items-center justify-center bg-gray-950/50 p-4`}
            onClick={(e) => {
                if (e.target === e.currentTarget && !preventClose && onClose) {
                    onClose();
                }
            }}
            role="presentation"
        >
            <div
                className={`relative w-full ${widthClass} rounded-xl bg-white p-5 shadow-2xl shadow-blue-950/20`}
                role="dialog"
                aria-modal="true"
            >
                {shouldShowClose && onClose && (
                    <button
                        type="button"
                        onClick={onClose}
                        className="absolute top-4 right-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"
                        aria-label="Close modal"
                    >
                        <XMarkIcon className="h-5 w-5" />
                    </button>
                )}

                <div className="flex items-start gap-3">
                    {icon && (
                        <div
                            className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${iconBg}`}
                        >
                            {icon}
                        </div>
                    )}

                    <div className="min-w-0 flex-1">
                        {title && (
                            <h2 className="text-lg font-bold text-gray-900">
                                {title}
                            </h2>
                        )}
                        {description && (
                            <p className="mt-1 text-sm text-gray-500">
                                {description}
                            </p>
                        )}
                    </div>
                </div>

                {children && <div className="mt-4 text-sm text-gray-600">{children}</div>}

                {footer && (
                    <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        {footer}
                    </div>
                )}
            </div>
        </div>
    );
}
