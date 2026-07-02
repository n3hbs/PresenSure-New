export const fieldFallback = "N/A";

export const getStatusVariant = (status) => {
    const normalized = String(status || "").toLowerCase();

    if (["active", "enrolled", "registered"].includes(normalized)) {
        return "green";
    }

    return "gray";
};

export const formatStudentName = (user = {}) =>
    [
        user.last_name,
        user.first_name,
        user.suffix,
        user.middle_initial ? `${user.middle_initial}.` : "",
    ]
        .filter(Boolean)
        .join(" ") || fieldFallback;

export const formatDisplayName = (user = {}) =>
    [user.first_name, user.middle_initial, user.last_name, user.suffix]
        .filter(Boolean)
        .join(" ") || fieldFallback;
