export default function StudentRegistrationField({
    label,
    name,
    value,
    onChange,
    required = false,
    maxLength,
    placeholder,
    disabled = false,
}) {
    return (
        <div>
            <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-400">
                {label}
                {required && <span className="text-red-500"> *</span>}
            </label>
            <input
                type="text"
                name={name}
                value={value}
                onChange={onChange}
                maxLength={maxLength}
                placeholder={placeholder}
                disabled={disabled}
                className={`h-11 w-full rounded-xl px-4 text-sm font-medium shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:ring-2 focus:ring-blue-100 ${
                    disabled
                        ? "bg-gray-100 text-gray-500"
                        : "bg-white text-gray-700"
                }`}
                autoComplete="off"
            />
        </div>
    );
}
