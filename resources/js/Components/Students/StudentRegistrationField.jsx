export default function StudentRegistrationField({
    label,
    name,
    value,
    onChange,
    required = false,
    maxLength,
    placeholder,
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
                className="h-11 w-full rounded-xl bg-white px-4 text-sm font-medium text-gray-700 shadow-sm shadow-blue-950/5 outline-none transition placeholder:text-gray-400 focus:ring-2 focus:ring-blue-100"
                autoComplete="off"
            />
        </div>
    );
}
