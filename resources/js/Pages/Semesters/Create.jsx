import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
    ArrowLeftIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import Stepper from "@/Components/UI/Stepper";
import DiscardRegistrationModal from "@/Components/UI/DiscardRegistrationModal";
import api from "@/Services/api";
import { notify } from "@/Services/toast";
import {
    schoolYearsQueryKey,
    semestersQueryKey,
    activeSemesterQueryKey,
} from "@/Services/queryKeys";
import usePermission from "@/Hooks/usePermission";

const PERIOD_CONFIG = [
    { key: "prelim", label: "Prelim", placeholder: "First examination period" },
    { key: "midterm", label: "Midterm", placeholder: "Mid-semester grading period" },
    { key: "prefinals", label: "Prefinals", placeholder: "Pre-final assessment period" },
    { key: "finals", label: "Finals", placeholder: "Culminating semester examination period" },
];

const TERMS = ["First Semester", "Second Semester", "Summer"];

const steps = [
    { number: 1, label: "Semester Details" },
    { number: 2, label: "Academic Periods" },
    { number: 3, label: "Review" },
];

const ReviewItem = ({ label, value }) => (
    <div className="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
        <p className="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
            {label}
        </p>
        <p className="mt-1 break-words text-sm font-semibold text-gray-900 dark:text-white">
            {value || "N/A"}
        </p>
    </div>
);

const ReviewGroup = ({ title, children }) => (
    <div className="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h2 className="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {title}
        </h2>
        <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {children}
        </div>
    </div>
);

export default function Create() {
    const { can, hasRole } = usePermission();
    const queryClient = useQueryClient();
    const [currentStep, setCurrentStep] = useState(1);

    // Permission guard
    useEffect(() => {
        if (!hasRole("administrator") && !can("semesters.manage")) {
            notify.error("Access Denied", "You do not have permission to create semesters.");
            router.visit("/semesters");
        }
    }, [can, hasRole]);

    // School years lookup
    const { data: schoolYears = [], isLoading: loadingSchoolYears } = useQuery({
        queryKey: schoolYearsQueryKey,
        queryFn: async () => {
            const res = await api.get("/v1/semesters/school-years");
            return res.data?.data || [];
        },
    });

    // Form states
    const [form, setForm] = useState({
        school_year_id: "",
        term: "First Semester",
        semester_start: "",
        semester_end: "",
        remarks: "",
    });

    useEffect(() => {
        if (schoolYears.length > 0 && !form.school_year_id) {
            setForm((prev) => ({ ...prev, school_year_id: String(schoolYears[0].school_year_id) }));
        }
    }, [schoolYears]);

    const [periods, setPeriods] = useState([
        { name: "prelim", enabled: true, period_start: "", period_end: "", description: "" },
        { name: "midterm", enabled: false, period_start: "", period_end: "", description: "" },
        { name: "prefinals", enabled: false, period_start: "", period_end: "", description: "" },
        { name: "finals", enabled: false, period_start: "", period_end: "", description: "" },
    ]);

    const [fieldErrors, setFieldErrors] = useState({});
    const [confirmDiscardOpen, setConfirmDiscardOpen] = useState(false);

    const todayDate = useMemo(() => new Date().toISOString().split("T")[0], []);

    const isDirty = useMemo(() => {
        return (
            currentStep > 1 ||
            Boolean(form.semester_start) ||
            Boolean(form.semester_end) ||
            Boolean(form.remarks) ||
            periods.some((p) => p.period_start || p.period_end)
        );
    }, [currentStep, form, periods]);

    const selectedSchoolYear = useMemo(() => {
        return schoolYears.find((sy) => String(sy.school_year_id) === String(form.school_year_id));
    }, [schoolYears, form.school_year_id]);

    const createMutation = useMutation({
        mutationFn: async (payload) => {
            const res = await api.post("/v1/semesters", payload);
            return res.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: semestersQueryKey });
            queryClient.invalidateQueries({ queryKey: activeSemesterQueryKey });
            notify.success(
                "Semester Registered",
                data?.message || "Academic semester and periods created successfully."
            );
            router.visit("/semesters");
        },
        onError: (err) => {
            const errors = err.response?.data?.data?.errors || {};
            setFieldErrors(errors);
            const msg =
                err.response?.data?.message ||
                "Failed to register semester. Please check the inputs.";
            notify.error("Registration Failed", msg);
        },
    });

    const handleTextChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (fieldErrors[name]) {
            setFieldErrors((prev) => {
                const next = { ...prev };
                delete next[name];
                return next;
            });
        }
    };

    const handlePeriodToggle = (index) => {
        setPeriods((prev) => {
            const updated = [...prev];
            const willEnable = !updated[index].enabled;

            if (!willEnable) {
                for (let i = index; i < updated.length; i++) {
                    updated[i].enabled = false;
                }
            } else {
                for (let i = 0; i <= index; i++) {
                    updated[i].enabled = true;
                }
            }
            return updated;
        });
    };

    const handlePeriodChange = (index, field, value) => {
        setPeriods((prev) => {
            const updated = [...prev];
            updated[index] = { ...updated[index], [field]: value };
            return updated;
        });
        const errKey = `period_${periods[index].name}_${field === "period_start" ? "start" : "end"}`;
        if (fieldErrors[errKey]) {
            setFieldErrors((prev) => {
                const next = { ...prev };
                delete next[errKey];
                return next;
            });
        }
    };

    const validateStep1 = () => {
        const errs = {};
        if (!form.school_year_id) {
            errs.school_year_id = ["School year is required."];
        }
        if (!form.term) {
            errs.term = ["Academic term is required."];
        }
        if (!form.semester_start) {
            errs.semester_start = ["Start date is required."];
        }
        if (!form.semester_end) {
            errs.semester_end = ["End date is required."];
        } else {
            if (form.semester_start && form.semester_end <= form.semester_start) {
                errs.semester_end = ["End date must be after the start date."];
            }
            if (form.semester_end < todayDate) {
                errs.semester_end = [
                    "The semester end date cannot be in the past. It must be today or an upcoming date.",
                ];
            }
        }

        setFieldErrors(errs);
        return Object.keys(errs).length === 0;
    };

    const validateStep2 = () => {
        const errs = {};
        const enabledPeriods = periods.filter((p) => p.enabled);

        let prevEnd = null;
        let prevName = null;

        for (let i = 0; i < enabledPeriods.length; i++) {
            const p = enabledPeriods[i];
            const pLabel = p.name.charAt(0).toUpperCase() + p.name.slice(1);

            if (!p.period_start) {
                errs[`period_${p.name}_start`] = [`${pLabel} start date is required.`];
            }
            if (!p.period_end) {
                errs[`period_${p.name}_end`] = [`${pLabel} end date is required.`];
            }

            if (p.period_start && p.period_end) {
                if (p.period_end < p.period_start) {
                    errs[`period_${p.name}_end`] = [`${pLabel} end date must be on or after start date.`];
                }

                if (p.period_start < form.semester_start || p.period_end > form.semester_end) {
                    errs[`period_${p.name}_start`] = [
                        `${pLabel} dates must be within semester dates (${form.semester_start} to ${form.semester_end}).`,
                    ];
                }

                if (prevEnd && p.period_start < prevEnd) {
                    errs[`period_${p.name}_start`] = [
                        `${pLabel} must start on or after ${prevName} ends (${prevEnd}).`,
                    ];
                }

                prevEnd = p.period_end;
                prevName = pLabel;
            }
        }

        setFieldErrors(errs);
        return Object.keys(errs).length === 0;
    };

    const handleNext = () => {
        if (currentStep === 1) {
            if (validateStep1()) {
                setCurrentStep(2);
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        } else if (currentStep === 2) {
            if (validateStep2()) {
                setCurrentStep(3);
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        }
    };

    const handleBack = () => {
        setFieldErrors({});
        setCurrentStep((prev) => Math.max(1, prev - 1));
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    const handleCancel = () => {
        if (isDirty) {
            setConfirmDiscardOpen(true);
        } else {
            router.visit("/semesters");
        }
    };

    const handleSubmit = () => {
        const enabledPeriods = periods
            .filter((p) => p.enabled && p.period_start && p.period_end)
            .map((p) => ({
                name: p.name,
                period_start: p.period_start,
                period_end: p.period_end,
                description: p.description || null,
            }));

        const payload = {
            school_year_id: Number(form.school_year_id),
            term: form.term,
            semester_start: form.semester_start,
            semester_end: form.semester_end,
            remarks: form.remarks || null,
            periods: enabledPeriods,
        };

        createMutation.mutate(payload);
    };

    const renderError = (field) =>
        fieldErrors[field]?.[0] ? (
            <p className="mt-1 text-xs font-medium text-red-500">
                {fieldErrors[field][0]}
            </p>
        ) : null;

    return (
        <MainLayout>
            <Head title="Create Semester - PresenSure" />

            <DiscardRegistrationModal
                open={confirmDiscardOpen}
                onKeepEditing={() => setConfirmDiscardOpen(false)}
                onDiscard={() => {
                    setConfirmDiscardOpen(false);
                    router.visit("/semesters");
                }}
            />

            {/* Same width layout as Student and Instructor registration pages */}
            <div className="space-y-6">
                {/* Breadcrumbs & Navigation Header (without page title) */}
                <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Semesters", href: "/semesters" },
                                { label: "Create Semester" },
                            ]}
                        />
                    </div>

                    <button
                        type="button"
                        onClick={handleCancel}
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700 dark:border dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Semesters
                    </button>
                </div>

                {/* Stepper Navigation */}
                <Stepper
                    steps={steps}
                    currentStep={currentStep}
                    onStepClick={(stepNumber) => {
                        if (stepNumber < currentStep) {
                            setCurrentStep(stepNumber);
                        } else if (stepNumber === 2 && currentStep === 1) {
                            if (validateStep1()) setCurrentStep(2);
                        } else if (stepNumber === 3) {
                            if (validateStep1() && validateStep2()) setCurrentStep(3);
                        }
                    }}
                />

                {/* ================= STEP 1: FORM ================= */}
                {currentStep === 1 && (
                    <section className="space-y-4 rounded-xl bg-white p-5 shadow-sm shadow-blue-950/5 dark:border dark:border-gray-800 dark:bg-gray-900">
                        {/* Section Header */}
                        <div className="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                <CalendarDaysIcon className="h-6 w-6" />
                            </div>
                            <div>
                                <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Semester Information
                                </h2>
                                <p className="text-sm text-gray-400 dark:text-gray-500">
                                    Specify the academic year, semester term, and duration dates.
                                </p>
                            </div>
                        </div>

                        {/* Fields Container */}
                        <div className="space-y-4">
                            <h3 className="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Academic Term & Duration
                            </h3>

                            <div className="grid gap-4 md:grid-cols-2">
                                {/* School Year */}
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        School Year <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="school_year_id"
                                        value={form.school_year_id}
                                        onChange={handleTextChange}
                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-white"
                                    >
                                        <option value="" disabled>
                                            {loadingSchoolYears ? "Loading school years..." : "Select school year"}
                                        </option>
                                        {schoolYears.map((sy) => (
                                            <option key={sy.school_year_id} value={sy.school_year_id}>
                                                Academic Year {sy.year_range} ({sy.school_year_start} - {sy.school_year_end})
                                            </option>
                                        ))}
                                    </select>
                                    {renderError("school_year_id")}
                                </div>

                                {/* Academic Term */}
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Academic Term <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="term"
                                        value={form.term}
                                        onChange={handleTextChange}
                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-white"
                                    >
                                        {TERMS.map((t) => (
                                            <option key={t} value={t}>
                                                {t}
                                            </option>
                                        ))}
                                    </select>
                                    {renderError("term")}
                                </div>

                                {/* Semester Start Date */}
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Semester Start Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="semester_start"
                                        value={form.semester_start}
                                        onChange={handleTextChange}
                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-white"
                                    />
                                    {renderError("semester_start")}
                                </div>

                                {/* Semester End Date */}
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Semester End Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="semester_end"
                                        min={todayDate}
                                        value={form.semester_end}
                                        onChange={handleTextChange}
                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2.5 px-3.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-white"
                                    />
                                    <p className="mt-1 text-[11px] text-gray-400">
                                        Must be today or an upcoming date.
                                    </p>
                                    {renderError("semester_end")}
                                </div>
                            </div>

                            {/* Remarks */}
                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    Administrative Remarks / Notes (Optional)
                                </label>
                                <textarea
                                    name="remarks"
                                    rows={3}
                                    value={form.remarks}
                                    onChange={handleTextChange}
                                    placeholder="Add any administrative remarks or calendar notes..."
                                    className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white p-3 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-white"
                                />
                                {renderError("remarks")}
                            </div>
                        </div>

                        {/* Action Buttons (Consistent with SingleRegistrationForm footer) */}
                        <div className="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end dark:border-gray-800">
                            <Button type="button" variant="outline" onClick={handleCancel}>
                                Cancel
                            </Button>
                            <Button type="button" onClick={handleNext}>
                                Next: Periods
                            </Button>
                        </div>
                    </section>
                )}

                {/* ================= STEP 2: PERIODS ================= */}
                {currentStep === 2 && (
                    <section className="space-y-4 rounded-xl bg-white p-5 shadow-sm shadow-blue-950/5 dark:border dark:border-gray-800 dark:bg-gray-900">
                        {/* Section Header */}
                        <div className="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                <ClockIcon className="h-6 w-6" />
                            </div>
                            <div>
                                <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Academic Periods Configuration
                                </h2>
                                <p className="text-sm text-gray-400 dark:text-gray-500">
                                    Configure examination periods in chronological order. Unconfigured periods can be added later.
                                </p>
                            </div>
                        </div>

                        {/* Sequential Rule Info */}
                        <div className="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/70 p-4 text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300">
                            <InformationCircleIcon className="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                            <div className="text-xs space-y-1">
                                <p className="font-semibold">Sequential Order Rule:</p>
                                <p>
                                    Periods must strictly follow chronological order (Prelim, Midterm, Prefinals, Finals) within the semester window (
                                    <span className="font-semibold">{form.semester_start}</span> to{" "}
                                    <span className="font-semibold">{form.semester_end}</span>).
                                </p>
                            </div>
                        </div>

                        {/* Period Cards */}
                        <div className="space-y-4">
                            {PERIOD_CONFIG.map((config, index) => {
                                const period = periods[index];
                                const isPreviousEnabled = index === 0 || periods[index - 1].enabled;

                                return (
                                    <div
                                        key={config.key}
                                        className={`rounded-xl border p-4 transition ${
                                            period.enabled
                                                ? "border-blue-200 bg-blue-50/20 dark:border-blue-900/40 dark:bg-blue-950/10"
                                                : "border-gray-200 bg-gray-50/50 opacity-75 dark:border-gray-800 dark:bg-gray-800/20"
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={`flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold ${
                                                        period.enabled
                                                            ? "bg-blue-600 text-white"
                                                            : "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                                                    }`}
                                                >
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <h3 className="text-sm font-bold text-gray-900 dark:text-white">
                                                        {config.label} Period
                                                    </h3>
                                                    <p className="text-xs text-gray-400 dark:text-gray-500">
                                                        {config.placeholder}
                                                    </p>
                                                </div>
                                            </div>

                                            {/* Enable Toggle */}
                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    checked={period.enabled}
                                                    disabled={index > 0 && !isPreviousEnabled}
                                                    onChange={() => handlePeriodToggle(index)}
                                                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50"
                                                />
                                                <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    {period.enabled ? "Configured" : "Add Later"}
                                                </span>
                                            </label>
                                        </div>

                                        {period.enabled && (
                                            <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                                        {config.label} Start Date <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        value={period.period_start}
                                                        min={
                                                            index > 0 && periods[index - 1].period_end
                                                                ? periods[index - 1].period_end
                                                                : form.semester_start
                                                        }
                                                        max={form.semester_end}
                                                        onChange={(e) =>
                                                            handlePeriodChange(index, "period_start", e.target.value)
                                                        }
                                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2 px-3 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                                    />
                                                    {renderError(`period_${config.key}_start`)}
                                                </div>

                                                <div>
                                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                                        {config.label} End Date <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        value={period.period_end}
                                                        min={period.period_start || form.semester_start}
                                                        max={form.semester_end}
                                                        onChange={(e) =>
                                                            handlePeriodChange(index, "period_end", e.target.value)
                                                        }
                                                        className="mt-1.5 w-full rounded-xl border border-gray-200 bg-white py-2 px-3 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                                    />
                                                    {renderError(`period_${config.key}_end`)}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>

                        {/* Action Buttons */}
                        <div className="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end dark:border-gray-800">
                            <Button type="button" variant="outline" onClick={handleBack}>
                                Back
                            </Button>
                            <Button type="button" onClick={handleNext}>
                                Next: Review
                            </Button>
                        </div>
                    </section>
                )}

                {/* ================= STEP 3: REVIEW ================= */}
                {currentStep === 3 && (
                    <section className="space-y-4 rounded-xl bg-white p-5 shadow-sm shadow-blue-950/5 dark:border dark:border-gray-800 dark:bg-gray-900">
                        {/* Section Header (Consistent with StudentRegistrationReview) */}
                        <div className="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-300">
                                <CheckCircleIcon className="h-6 w-6" />
                            </div>
                            <div>
                                <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Review Semester & Periods
                                </h2>
                                <p className="text-sm text-gray-400 dark:text-gray-500">
                                    Confirm the semester details and examination periods before saving.
                                </p>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <ReviewGroup title="Semester Overview">
                                <ReviewItem
                                    label="School Year"
                                    value={selectedSchoolYear ? `AY ${selectedSchoolYear.year_range}` : "N/A"}
                                />
                                <ReviewItem label="Academic Term" value={form.term} />
                                <ReviewItem label="Start Date" value={form.semester_start} />
                                <ReviewItem label="End Date" value={form.semester_end} />
                                <ReviewItem label="Remarks" value={form.remarks || "No remarks provided"} />
                            </ReviewGroup>

                            {/* Periods Review */}
                            <div className="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                                <h3 className="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
                                    Configured Academic Periods
                                </h3>

                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                                    <table className="w-full text-left text-sm">
                                        <thead className="border-b border-gray-100 bg-gray-50/75 text-xs uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-800/50">
                                            <tr>
                                                <th className="px-4 py-3">Sequence</th>
                                                <th className="px-4 py-3">Period</th>
                                                <th className="px-4 py-3">Start Date</th>
                                                <th className="px-4 py-3">End Date</th>
                                                <th className="px-4 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                            {periods.map((p, idx) => {
                                                const label = p.name.charAt(0).toUpperCase() + p.name.slice(1);
                                                const isConfigured = p.enabled && p.period_start && p.period_end;

                                                return (
                                                    <tr key={p.name}>
                                                        <td className="px-4 py-3 font-semibold text-gray-400">
                                                            #{idx + 1}
                                                        </td>
                                                        <td className="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                                            {label}
                                                        </td>
                                                        <td className="px-4 py-3 text-gray-600 dark:text-gray-300">
                                                            {isConfigured ? p.period_start : "—"}
                                                        </td>
                                                        <td className="px-4 py-3 text-gray-600 dark:text-gray-300">
                                                            {isConfigured ? p.period_end : "—"}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            {isConfigured ? (
                                                                <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full dark:bg-emerald-950/40 dark:text-emerald-300">
                                                                    <CheckCircleIcon className="h-3.5 w-3.5" />
                                                                    Configured
                                                                </span>
                                                            ) : (
                                                                <span className="inline-flex items-center text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full dark:bg-gray-800 dark:text-gray-400">
                                                                    Follow up later
                                                                </span>
                                                            )}
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {/* Action Buttons (Consistent with StudentRegistrationReview footer) */}
                        <div className="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end dark:border-gray-800">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleBack}
                                disabled={createMutation.isPending}
                            >
                                Back
                            </Button>
                            <Button
                                type="button"
                                onClick={handleSubmit}
                                disabled={createMutation.isPending}
                            >
                                {createMutation.isPending ? "Saving..." : "Save Semester"}
                            </Button>
                        </div>
                    </section>
                )}
            </div>
        </MainLayout>
    );
}
