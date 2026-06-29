import { useCallback, useEffect, useMemo, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PhotoIcon,
    UserPlusIcon,
    XCircleIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import SelectDropdown from "@/Components/UI/SelectDropdown";
import api from "@/Services/api";
import {
    activeStudentsQueryKey,
    departmentsQueryKey,
    programsQueryKey,
} from "@/Services/queryKeys";

const emptyForm = {
    user_id: "",
    first_name: "",
    middle_initial: "",
    last_name: "",
    suffix: "",
    sex: "",
    department_id: "",
    program_id: "",
    year: "",
    block: "",
};

const yearLabels = ["First Year", "Second Year", "Third Year", "Fourth Year"];
const blockOptions = ["A", "B", "C"].map((block) => ({
    label: block,
    value: block,
}));
const sexOptions = [
    { label: "Male", value: "male" },
    { label: "Female", value: "female" },
];

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const makeStudentId = (value) => {
    const numbersOnly = value.replace(/[^0-9]/g, "").slice(0, 8);

    if (!numbersOnly) return "";

    const prefix = numbersOnly.slice(0, 4);
    const suffix = numbersOnly.slice(4);

    return suffix ? `C-${prefix}-${suffix}` : `C-${prefix}`;
};

const Field = ({
    label,
    name,
    value,
    onChange,
    required = false,
    maxLength,
    placeholder,
}) => (
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

const toastStyles = {
    success: {
        icon: CheckCircleIcon,
        container: "border-green-100 bg-green-50 text-green-800",
        iconClass: "text-green-600",
    },
    warning: {
        icon: ExclamationTriangleIcon,
        container: "border-amber-100 bg-amber-50 text-amber-800",
        iconClass: "text-amber-600",
    },
    error: {
        icon: XCircleIcon,
        container: "border-red-100 bg-red-50 text-red-800",
        iconClass: "text-red-600",
    },
};

const Toast = ({ toast, onClose }) => {
    if (!toast) return null;

    const style = toastStyles[toast.type] || toastStyles.success;
    const Icon = style.icon;

    return (
        <div className="fixed right-4 top-4 z-100 w-[calc(100%-2rem)] max-w-sm">
            <div
                className={`flex items-start gap-3 rounded-xl border p-4 shadow-2xl shadow-blue-950/10 ${style.container}`}
                role="status"
            >
                <Icon
                    className={`mt-0.5 h-5 w-5 flex-shrink-0 ${style.iconClass}`}
                />
                <div className="min-w-0 flex-1">
                    <p className="text-sm font-bold">{toast.title}</p>
                    {toast.message && (
                        <p className="mt-1 text-sm opacity-90">
                            {toast.message}
                        </p>
                    )}
                </div>
                <button
                    type="button"
                    onClick={onClose}
                    className="rounded-full p-1 opacity-70 transition hover:bg-white/60 hover:opacity-100"
                    aria-label="Close notification"
                >
                    <XMarkIcon className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
};

const DiscardModal = ({ open, onKeepEditing, onDiscard }) => {
    if (!open) return null;

    return (
        <div className="fixed inset-0 z-90 flex items-center justify-center bg-gray-950/50 p-4">
            <div
                className="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl shadow-blue-950/20"
                role="dialog"
                aria-modal="true"
                aria-labelledby="discard-title"
            >
                <div className="flex items-start gap-3">
                    <div className="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <ExclamationTriangleIcon className="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            id="discard-title"
                            className="text-lg font-bold text-gray-900"
                        >
                            Discard registration?
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            You have filled up inputs already. Leaving this page
                            will clear the registration form.
                        </p>
                    </div>
                </div>

                <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" onClick={onDiscard}>
                        Discard
                    </Button>
                    <Button type="button" onClick={onKeepEditing}>
                        Keep Editing
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default function ManualRegistration() {
    const queryClient = useQueryClient();
    const [form, setForm] = useState(emptyForm);
    const [image, setImage] = useState(null);
    const [imagePreview, setImagePreview] = useState("");
    const [submitting, setSubmitting] = useState(false);
    const [fieldErrors, setFieldErrors] = useState({});
    const [toast, setToast] = useState(null);
    const [confirmDiscardOpen, setConfirmDiscardOpen] = useState(false);

    const showToast = useCallback((type, title, message = "") => {
        setToast({ type, title, message, id: Date.now() });
    }, []);

    const isDirty = useMemo(
        () =>
            Object.values(form).some((value) => String(value || "").trim()) ||
            Boolean(image),
        [form, image],
    );

    const getAuthHeaders = () => {
        const token = sessionStorage.getItem("token");
        return token ? { Authorization: `Bearer ${token}` } : {};
    };

    const {
        data: departments = [],
        isLoading: loadingDepartments,
        isError: departmentsError,
        error: departmentRequestError,
    } = useQuery({
        queryKey: departmentsQueryKey,
        queryFn: async () => {
            const response = await api.get("/departments", {
                headers: getAuthHeaders(),
            });

            return getCollection(response);
        },
    });

    const {
        data: programs = [],
        isLoading: loadingPrograms,
        isError: programsError,
        error: programRequestError,
    } = useQuery({
        queryKey: programsQueryKey,
        queryFn: async () => {
            const response = await api.get("/programs", {
                headers: getAuthHeaders(),
            });

            return getCollection(response);
        },
    });

    const loadingOptions = loadingDepartments || loadingPrograms;

    useEffect(() => {
        if (!toast) return undefined;

        const timeout = window.setTimeout(() => setToast(null), 5000);

        return () => window.clearTimeout(timeout);
    }, [toast]);

    useEffect(() => {
        const handleBeforeUnload = (event) => {
            if (!isDirty) return;

            event.preventDefault();
            event.returnValue = "";
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () =>
            window.removeEventListener("beforeunload", handleBeforeUnload);
    }, [isDirty]);

    useEffect(() => {
        if (!departmentsError) return;

        showToast(
            "error",
            "Unable to load departments",
            departmentRequestError?.response?.data?.message ||
                "Unable to load departments.",
        );
    }, [departmentRequestError, departmentsError, showToast]);

    useEffect(() => {
        if (!programsError) return;

        showToast(
            "error",
            "Unable to load programs",
            programRequestError?.response?.data?.message ||
                "Unable to load programs.",
        );
    }, [programRequestError, programsError, showToast]);

    useEffect(() => {
        if (!image) {
            setImagePreview("");
            return;
        }

        const previewUrl = URL.createObjectURL(image);
        setImagePreview(previewUrl);

        return () => URL.revokeObjectURL(previewUrl);
    }, [image]);

    const departmentOptions = useMemo(
        () =>
            departments.map((department) => ({
                label: department.department_name,
                value: String(department.department_id),
            })),
        [departments],
    );

    const filteredPrograms = useMemo(
        () =>
            programs.filter(
                (program) =>
                    String(program.department?.department_id) ===
                    String(form.department_id),
            ),
        [form.department_id, programs],
    );

    const programOptions = useMemo(
        () =>
            filteredPrograms.map((program) => ({
                label: `${program.program_code} - ${program.program_name}`,
                value: String(program.program_id),
            })),
        [filteredPrograms],
    );

    const selectedProgram = useMemo(
        () =>
            programs.find(
                (program) =>
                    String(program.program_id) === String(form.program_id),
            ),
        [form.program_id, programs],
    );

    const yearOptions = useMemo(() => {
        const totalYears = Math.min(
            Number(selectedProgram?.program_years) || 4,
            yearLabels.length,
        );

        return yearLabels.slice(0, totalYears).map((year) => ({
            label: year,
            value: year,
        }));
    }, [selectedProgram]);

    const handleChange = (event) => {
        const { name, value } = event.target;

        setFieldErrors((current) => ({ ...current, [name]: null }));

        if (name === "user_id") {
            setForm((current) => ({
                ...current,
                user_id: makeStudentId(value),
            }));
            return;
        }

        if (name === "middle_initial") {
            setForm((current) => ({
                ...current,
                middle_initial: value.toUpperCase().slice(0, 5),
            }));
            return;
        }

        setForm((current) => ({ ...current, [name]: value }));
    };

    const updateSelect = (name, value) => {
        setFieldErrors((current) => ({ ...current, [name]: null }));
        setForm((current) => {
            const next = { ...current, [name]: value };

            if (name === "department_id") {
                next.program_id = "";
                next.year = "";
            }

            if (name === "program_id") {
                next.year = "";
            }

            return next;
        });
    };

    const handleImageChange = (event) => {
        const selectedImage = event.target.files?.[0] || null;
        setFieldErrors((current) => ({ ...current, image: null }));
        setImage(selectedImage);

        if (selectedImage) {
            showToast(
                "success",
                "Image selected",
                `${selectedImage.name} is ready to upload.`,
            );
        }
    };

    const validate = () => {
        const requiredFields = [
            "user_id",
            "first_name",
            "last_name",
            "sex",
            "department_id",
            "program_id",
            "year",
            "block",
        ];
        const nextErrors = {};

        requiredFields.forEach((field) => {
            if (!String(form[field] || "").trim()) {
                nextErrors[field] = ["This field is required."];
            }
        });

        setFieldErrors(nextErrors);
        return Object.keys(nextErrors).length === 0;
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!validate()) {
            showToast(
                "warning",
                "Missing required fields",
                "Please fill in the highlighted fields before registering.",
            );
            return;
        }

        setSubmitting(true);

        const payload = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (key !== "department_id") {
                payload.append(key, value);
            }
        });

        if (image) {
            payload.append("image", image);
        }

        try {
            const token = sessionStorage.getItem("token");
            const response = await api.post(
                "/student/registerStudent",
                payload,
                {
                    headers: {
                        ...(token ? { Authorization: `Bearer ${token}` } : {}),
                        "Content-Type": "multipart/form-data",
                    },
                },
            );

            showToast(
                "success",
                "Registration complete",
                response.data?.message || "Student registered successfully.",
            );
            setForm(emptyForm);
            setImage(null);
            setFieldErrors({});
            queryClient.invalidateQueries({
                queryKey: activeStudentsQueryKey,
            });
        } catch (requestError) {
            if (requestError.response?.status === 422) {
                setFieldErrors(requestError.response.data.errors || {});
                showToast(
                    "warning",
                    "Please check the form",
                    "Some fields need your attention before this can be submitted.",
                );
            } else {
                showToast(
                    "error",
                    "Registration failed",
                    requestError.response?.data?.message ||
                        "Student registration failed.",
                );
            }
        } finally {
            setSubmitting(false);
        }
    };

    const requestStudentsPage = () => {
        if (isDirty) {
            setConfirmDiscardOpen(true);
            return;
        }

        router.visit("/students");
    };

    const discardAndLeave = () => {
        setConfirmDiscardOpen(false);
        router.visit("/students");
    };

    const renderError = (name) =>
        fieldErrors[name]?.[0] ? (
            <p className="mt-1 text-xs font-medium text-red-500">
                {fieldErrors[name][0]}
            </p>
        ) : null;

    return (
        <>
            <Head title="Single Registration" />
            <Toast toast={toast} onClose={() => setToast(null)} />
            <DiscardModal
                open={confirmDiscardOpen}
                onKeepEditing={() => setConfirmDiscardOpen(false)}
                onDiscard={discardAndLeave}
            />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Students", href: "/students" },
                            { label: "Single Registration" },
                        ]}
                    />

                    <button
                        type="button"
                        onClick={requestStudentsPage}
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Students
                    </button>
                </div>

                <form
                    onSubmit={handleSubmit}
                    className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"
                >
                    <section className="space-y-4 rounded-xl bg-white p-5 shadow-sm shadow-blue-950/5">
                        <div className="flex items-center gap-3 border-b border-gray-100 pb-4">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                <UserPlusIcon className="h-6 w-6" />
                            </div>
                            <div>
                                <h1 className="text-lg font-bold text-gray-900">
                                    Single Student Registration
                                </h1>
                                <p className="text-sm text-gray-400">
                                    Register one student for the active
                                    semester.
                                </p>
                            </div>
                        </div>

                        <div>
                            <h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">
                                Personal Information
                            </h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Field
                                        label="Student Number"
                                        name="user_id"
                                        value={form.user_id}
                                        onChange={handleChange}
                                        required
                                        maxLength={11}
                                        placeholder="C-0000-0000"
                                    />
                                    {renderError("user_id")}
                                </div>
                                <div>
                                    <SelectDropdown
                                        label="Sex"
                                        options={sexOptions}
                                        value={form.sex}
                                        onChange={(value) =>
                                            updateSelect("sex", value)
                                        }
                                        placeholder="Select sex"
                                        buttonClassName="bg-white"
                                    />
                                    {renderError("sex")}
                                </div>
                                <div>
                                    <Field
                                        label="First Name"
                                        name="first_name"
                                        value={form.first_name}
                                        onChange={handleChange}
                                        required
                                    />
                                    {renderError("first_name")}
                                </div>
                                <div>
                                    <Field
                                        label="Last Name"
                                        name="last_name"
                                        value={form.last_name}
                                        onChange={handleChange}
                                        required
                                    />
                                    {renderError("last_name")}
                                </div>
                                <div>
                                    <Field
                                        label="Middle Initial"
                                        name="middle_initial"
                                        value={form.middle_initial}
                                        onChange={handleChange}
                                        maxLength={5}
                                    />
                                    {renderError("middle_initial")}
                                </div>
                                <div>
                                    <Field
                                        label="Suffix"
                                        name="suffix"
                                        value={form.suffix}
                                        onChange={handleChange}
                                        maxLength={10}
                                    />
                                    {renderError("suffix")}
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">
                                Academic Information
                            </h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <SelectDropdown
                                        label="Department"
                                        options={departmentOptions}
                                        value={form.department_id}
                                        onChange={(value) =>
                                            updateSelect("department_id", value)
                                        }
                                        placeholder={
                                            loadingOptions
                                                ? "Loading..."
                                                : "Select department"
                                        }
                                        buttonClassName="bg-white"
                                    />
                                    {renderError("department_id")}
                                </div>
                                <div>
                                    <SelectDropdown
                                        label="Program"
                                        options={programOptions}
                                        value={form.program_id}
                                        onChange={(value) =>
                                            updateSelect("program_id", value)
                                        }
                                        placeholder="Select program"
                                        buttonClassName="bg-white"
                                    />
                                    {renderError("program_id")}
                                </div>
                                <div>
                                    <SelectDropdown
                                        label="Year Level"
                                        options={yearOptions}
                                        value={form.year}
                                        onChange={(value) =>
                                            updateSelect("year", value)
                                        }
                                        placeholder="Select year"
                                        buttonClassName="bg-white"
                                    />
                                    {renderError("year")}
                                </div>
                                <div>
                                    <SelectDropdown
                                        label="Block"
                                        options={blockOptions}
                                        value={form.block}
                                        onChange={(value) =>
                                            updateSelect("block", value)
                                        }
                                        placeholder="Select block"
                                        buttonClassName="bg-white"
                                    />
                                    {renderError("block")}
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={requestStudentsPage}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={submitting}>
                                {submitting
                                    ? "Registering..."
                                    : "Register Student"}
                            </Button>
                        </div>
                    </section>

                    <aside className="h-fit rounded-xl bg-white p-5 shadow-sm shadow-blue-950/5">
                        <h2 className="mb-4 text-sm font-bold uppercase tracking-wide text-gray-500">
                            Profile Image
                        </h2>

                        <label
                            htmlFor="student-image"
                            className="flex aspect-square w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border border-dashed border-blue-200 bg-blue-50/50 text-blue-700 transition hover:border-blue-400 hover:bg-blue-50"
                        >
                            {imagePreview ? (
                                <img
                                    src={imagePreview}
                                    alt="Student preview"
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex flex-col items-center gap-2 text-center">
                                    <PhotoIcon className="h-10 w-10" />
                                    <span className="text-sm font-semibold">
                                        Upload image
                                    </span>
                                </div>
                            )}
                            <input
                                id="student-image"
                                type="file"
                                accept="image/*"
                                onChange={handleImageChange}
                                className="hidden"
                            />
                        </label>

                        {image && (
                            <div className="mt-3 flex items-center justify-between gap-3">
                                <p className="truncate text-sm font-medium text-gray-600">
                                    {image.name}
                                </p>
                                <button
                                    type="button"
                                    onClick={() => setImage(null)}
                                    className="text-sm font-semibold text-red-600 hover:text-red-700"
                                >
                                    Remove
                                </button>
                            </div>
                        )}
                        {renderError("image")}
                    </aside>
                </form>
            </div>
        </>
    );
}

ManualRegistration.layout = (page) => <MainLayout>{page}</MainLayout>;
