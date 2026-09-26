import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
    ArrowLeftIcon,
    ArrowPathIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    LockClosedIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import Stepper from "@/Components/UI/Stepper";
import DiscardRegistrationModal from "@/Components/UI/DiscardRegistrationModal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
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

export default function Edit({ semesterId: propSemesterId }) {
    const { can, hasRole } = usePermission();
    const queryClient = useQueryClient();

    const params = typeof window !== "undefined" ? new URLSearchParams(window.location.search) : new URLSearchParams();
    const semesterId = propSemesterId || params.get("semester_id") || params.get("id");

    const [currentStep, setCurrentStep] = useState(1);

    // Permission guard
    useEffect(() => {
        if (!hasRole("administrator") && !can("semesters.manage")) {
            notify.error("Access Denied", "You do not have permission to edit semesters.");
            router.visit("/semesters");
        }
    }, [can, hasRole]);

    // School years lookup
    const { data: schoolYears = [], isLoading: loadingSchoolYears } = useQuery({
        queryKey: schoolYearsQueryKey,
        enabled: Boolean(getAuthToken()),
        queryFn: async () => {
            const token = getAuthToken();
            const res = await api.get("/v1/semesters/school-years", {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return res.data?.data || [];
        },
    });

    // Fetch existing semester
    const {
        data: semester,
        isLoading: loadingSemester,
        isError: semesterError,
    } = useQuery({
        queryKey: ["semesters", semesterId],
        enabled: Boolean(semesterId) && Boolean(getAuthToken()),
        queryFn: async () => {
            const token = getAuthToken();
            const res = await api.get(`/v1/semesters/${semesterId}`, {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return res.data?.data;
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

    const [periods, setPeriods] = useState([
        { name: "prelim", enabled: false, period_id: null, period_start: "", period_end: "", description: "", hasAttendanceSessions: false },
        { name: "midterm", enabled: false, period_id: null, period_start: "", period_end: "", description: "", hasAttendanceSessions: false },
        { name: "prefinals", enabled: false, period_id: null, period_start: "", period_end: "", description: "", hasAttendanceSessions: false },
        { name: "finals", enabled: false, period_id: null, period_start: "", period_end: "", description: "", hasAttendanceSessions: false },
    ]);

    const [fieldErrors, setFieldErrors] = useState({});
    const [confirmDiscardOpen, setConfirmDiscardOpen] = useState(false);
    const [isInitialized, setIsInitialized] = useState(false);

    // Initialize form when semester data is fetched
    useEffect(() => {
        if (!semester || isInitialized) return;

        setForm({
            school_year_id: String(semester.school_year?.school_year_id || semester.school_year_id || ""),
            term: semester.term || "First Semester",
            semester_start: semester.semester_start || "",
            semester_end: semester.semester_end || "",
            remarks: semester.remarks || "",
        });

        const existingPeriodsMap = {};
        if (Array.isArray(semester.periods)) {
            semester.periods.forEach((p) => {
                existingPeriodsMap[p.name.toLowerCase()] = p;
            });
        }

        setPeriods(
            PERIOD_CONFIG.map(({ key }) => {
                const existing = existingPeriodsMap[key];
                return {
                    name: key,
                    enabled: Boolean(existing),
                    period_id: existing?.period_id || null,
                    period_start: existing?.period_start || "",
                    period_end: existing?.period_end || "",
                    description: existing?.description || "",
                    hasAttendanceSessions: Boolean(existing?.attendance_sessions_count > 0 || (existing?.attendance_sessions && existing.attendance_sessions.length > 0)),
                };
            })
        );

        setIsInitialized(true);
    }, [semester, isInitialized]);

    const todayDate = useMemo(() => new Date().toISOString().split("T")[0], []);

    const selectedSchoolYear = useMemo(() => {
        return schoolYears.find((sy) => String(sy.school_year_id) === String(form.school_year_id));
    }, [schoolYears, form.school_year_id]);

    const updateMutation = useMutation({
        mutationFn: async (payload) => {
            const token = getAuthToken();
            const res = await api.put(`/v1/semesters/${semesterId}`, payload, {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return res.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: semestersQueryKey });
            queryClient.invalidateQueries({ queryKey: ["semesters", semesterId] });
            queryClient.invalidateQueries({ queryKey: activeSemesterQueryKey });
            notify.success(
                "Semester Updated",
                data?.message || "Academic semester and periods updated successfully."
            );
            router.visit("/semesters");
        },
        onError: (err) => {
            const errors = err.response?.data?.data?.errors || err.response?.data?.errors || {};
            setFieldErrors(errors);
            const msg =
                err.response?.data?.message ||
                "Failed to update semester. Please check the inputs.";
            notify.error("Update Failed", msg);
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
            const target = updated[index];

            // If period has attendance sessions, prevent disabling
            if (target.hasAttendanceSessions && target.enabled) {
                notify.warning(
                    "Action Restricted",
                    `Cannot disable the ${target.name.toUpperCase()} period because attendance sessions have already been recorded for it.`
                );
                return prev;
            }

            const willEnable = !target.enabled;

            if (!willEnable) {
                // If disabling, disable this and all subsequent
                for (let i = index; i < updated.length; i++) {
                    if (updated[i].hasAttendanceSessions) {
                        notify.warning(
                            "Action Restricted",
                            `Cannot disable subsequent period ${updated[i].name.toUpperCase()} with active attendance records.`
                        );
                        return prev;
                    }
                    updated[i].enabled = false;
                }
            } else {
                // If enabling, enable all prior
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
            if (validateStep1()) setCurrentStep(2);
        } else if (currentStep === 2) {
            if (validateStep2()) setCurrentStep(3);
        }
    };

    const handlePrev = () => {
        if (currentStep > 1) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const handleSubmit = () => {
        if (!validateStep1() || !validateStep2()) return;

        const activePeriods = periods
            .filter((p) => p.enabled)
            .map((p) => ({
                period_id: p.period_id || undefined,
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
            periods: activePeriods,
        };

        updateMutation.mutate(payload);
    };

    if (loadingSemester) {
        return (
            <div className="flex h-96 items-center justify-center">
                <div className="flex flex-col items-center gap-3">
                    <ArrowPathIcon className="h-8 w-8 animate-spin text-blue-600" />
                    <p className="text-sm font-medium text-gray-500">Loading semester details...</p>
                </div>
            </div>
        );
    }

    if (semesterError || !semester) {
        return (
            <div className="rounded-2xl border border-red-200 bg-red-50 p-8 text-center dark:border-red-900/50 dark:bg-red-950/20">
                <ExclamationTriangleIcon className="mx-auto h-12 w-12 text-red-500" />
                <h3 className="mt-3 text-lg font-bold text-red-900 dark:text-red-200">
                    Semester Not Found
                </h3>
                <p className="mt-1 text-sm text-red-700 dark:text-red-300">
                    The requested semester could not be located or has been archived.
                </p>
                <div className="mt-5">
                    <Button onClick={() => router.visit("/semesters")}>
                        Return to Semesters
                    </Button>
                </div>
            </div>
        );
    }

    return (
        <>
            <Head title={`Edit Semester - ${semester.term || "Academic Period"}`} />

            <div className="space-y-6">
                {/* Header & Breadcrumbs */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Semesters", href: "/semesters" },
                            { label: "Edit Semester" },
                        ]}
                    />

                    <Button
                        variant="secondary"
                        onClick={() => router.visit("/semesters")}
                        className="inline-flex items-center gap-2 text-sm"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Semesters
                    </Button>
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

                {/* Step 1: Semester Details Form */}
                {currentStep === 1 && (
                    <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div className="border-b border-gray-100 pb-4 dark:border-gray-800">
                            <h2 className="text-base font-bold text-gray-900 dark:text-white">
                                Academic Semester Information
                            </h2>
                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Update the institutional school year, term designation, and academic period bounds.
                            </p>
                        </div>

                        <div className="mt-6 space-y-6">
                            {/* School Year & Term */}
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        School Year <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="school_year_id"
                                        value={form.school_year_id}
                                        onChange={handleTextChange}
                                        className="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option value="" disabled>Select School Year</option>
                                        {schoolYears.map((sy) => (
                                            <option key={sy.school_year_id} value={sy.school_year_id}>
                                                {sy.year_range} {sy.is_current ? "(Current AY)" : ""}
                                            </option>
                                        ))}
                                    </select>
                                    {fieldErrors.school_year_id && (
                                        <p className="mt-1 text-xs text-red-500">{fieldErrors.school_year_id[0]}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        Academic Term <span className="text-red-500">*</span>
                                    </label>
                                    <div className="mt-1.5 grid grid-cols-3 gap-2">
                                        {TERMS.map((t) => (
                                            <button
                                                key={t}
                                                type="button"
                                                onClick={() => setForm((prev) => ({ ...prev, term: t }))}
                                                className={`rounded-xl border py-2.5 px-3 text-xs font-semibold transition ${
                                                    form.term === t
                                                        ? "border-blue-600 bg-blue-50 text-blue-700 dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-300 shadow-sm"
                                                        : "border-gray-200 bg-white text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                }`}
                                            >
                                                {t}
                                            </button>
                                        ))}
                                    </div>
                                    {fieldErrors.term && (
                                        <p className="mt-1 text-xs text-red-500">{fieldErrors.term[0]}</p>
                                    )}
                                </div>
                            </div>

                            {/* Semester Dates */}
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        Semester Start Date <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative mt-1.5">
                                        <input
                                            type="date"
                                            name="semester_start"
                                            value={form.semester_start}
                                            onChange={handleTextChange}
                                            className="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    {fieldErrors.semester_start && (
                                        <p className="mt-1 text-xs text-red-500">{fieldErrors.semester_start[0]}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        Semester End Date <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative mt-1.5">
                                        <input
                                            type="date"
                                            name="semester_end"
                                            value={form.semester_end}
                                            onChange={handleTextChange}
                                            className="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    {fieldErrors.semester_end && (
                                        <p className="mt-1 text-xs text-red-500">{fieldErrors.semester_end[0]}</p>
                                    )}
                                </div>
                            </div>

                            {/* Remarks */}
                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                    Remarks / Notes (Optional)
                                </label>
                                <textarea
                                    name="remarks"
                                    rows={3}
                                    value={form.remarks}
                                    onChange={handleTextChange}
                                    placeholder="Institutional notes or directives..."
                                    className="mt-1.5 w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                                {fieldErrors.remarks && (
                                    <p className="mt-1 text-xs text-red-500">{fieldErrors.remarks[0]}</p>
                                )}
                            </div>
                        </div>

                        {/* Footer Buttons */}
                        <div className="mt-8 flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
                            <Button
                                variant="secondary"
                                onClick={() => router.visit("/semesters")}
                            >
                                Cancel
                            </Button>
                            <Button onClick={handleNext}>
                                Next: Academic Periods →
                            </Button>
                        </div>
                    </div>
                )}

                {/* Step 2: Academic Periods Form */}
                {currentStep === 2 && (
                    <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div className="border-b border-gray-100 pb-4 dark:border-gray-800">
                            <h2 className="text-base font-bold text-gray-900 dark:text-white">
                                Academic Evaluation Periods
                            </h2>
                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Configure the 4 grading periods (Prelim, Midterm, Prefinals, Finals) for this semester.
                                Periods must occur in chronological order within the semester bounds ({form.semester_start} to {form.semester_end}).
                            </p>
                        </div>

                        <div className="mt-6 space-y-4">
                            {periods.map((period, index) => {
                                const config = PERIOD_CONFIG[index];
                                const isEnabled = period.enabled;
                                const isLocked = period.hasAttendanceSessions;

                                return (
                                    <div
                                        key={period.name}
                                        className={`rounded-xl border transition-all ${
                                            isEnabled
                                                ? "border-blue-200 bg-white p-5 shadow-sm dark:border-blue-900/60 dark:bg-gray-800/80"
                                                : "border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40 opacity-75"
                                        }`}
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div className="flex items-center gap-3">
                                                <button
                                                    type="button"
                                                    onClick={() => handlePeriodToggle(index)}
                                                    className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                                                        isEnabled ? "bg-blue-600" : "bg-gray-300 dark:bg-gray-700"
                                                    }`}
                                                >
                                                    <span
                                                        className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out ${
                                                            isEnabled ? "translate-x-5" : "translate-x-0"
                                                        }`}
                                                    />
                                                </button>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <h3 className="text-sm font-bold text-gray-900 dark:text-white">
                                                            {config.label} Period
                                                        </h3>
                                                        {isEnabled && (
                                                            <span className="rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 uppercase tracking-wider">
                                                                Active
                                                            </span>
                                                        )}
                                                        {isLocked && (
                                                            <span className="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                                                <LockClosedIcon className="h-3 w-3" />
                                                                Attendance Recorded
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                                        {config.placeholder}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {isEnabled && (
                                            <div className="mt-4 grid grid-cols-1 gap-4 pt-3 border-t border-gray-100 dark:border-gray-800/80 sm:grid-cols-2 lg:grid-cols-3">
                                                <div>
                                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                                        Start Date <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        value={period.period_start}
                                                        min={form.semester_start}
                                                        max={form.semester_end}
                                                        onChange={(e) =>
                                                            handlePeriodChange(index, "period_start", e.target.value)
                                                        }
                                                        className="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    />
                                                    {fieldErrors[`period_${period.name}_start`] && (
                                                        <p className="mt-1 text-xs text-red-500">
                                                            {fieldErrors[`period_${period.name}_start`][0]}
                                                        </p>
                                                    )}
                                                </div>

                                                <div>
                                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                                        End Date <span className="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        value={period.period_end}
                                                        min={period.period_start || form.semester_start}
                                                        max={form.semester_end}
                                                        onChange={(e) =>
                                                            handlePeriodChange(index, "period_end", e.target.value)
                                                        }
                                                        className="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    />
                                                    {fieldErrors[`period_${period.name}_end`] && (
                                                        <p className="mt-1 text-xs text-red-500">
                                                            {fieldErrors[`period_${period.name}_end`][0]}
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="sm:col-span-2 lg:col-span-1">
                                                    <label className="block text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                                        Notes / Description
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={period.description}
                                                        placeholder="e.g. Major exams week"
                                                        onChange={(e) =>
                                                            handlePeriodChange(index, "description", e.target.value)
                                                        }
                                                        className="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    />
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>

                        {fieldErrors.periods && (
                            <div className="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                                {Array.isArray(fieldErrors.periods) ? fieldErrors.periods[0] : fieldErrors.periods}
                            </div>
                        )}

                        {/* Footer Buttons */}
                        <div className="mt-8 flex items-center justify-between border-t border-gray-100 pt-5 dark:border-gray-800">
                            <Button variant="secondary" onClick={handlePrev}>
                                ← Back: Semester Details
                            </Button>
                            <Button onClick={handleNext}>
                                Next: Review Changes →
                            </Button>
                        </div>
                    </div>
                )}

                {/* Step 3: Review & Submit */}
                {currentStep === 3 && (
                    <div className="space-y-6">
                        <ReviewGroup title="Semester Overview">
                            <ReviewItem
                                label="School Year"
                                value={selectedSchoolYear ? `A.Y. ${selectedSchoolYear.year_range}` : "—"}
                            />
                            <ReviewItem label="Term" value={form.term} />
                            <ReviewItem
                                label="Duration"
                                value={`${form.semester_start} to ${form.semester_end}`}
                            />
                            <ReviewItem label="Remarks" value={form.remarks || "No remarks"} />
                        </ReviewGroup>

                        <div className="rounded-xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h2 className="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Academic Evaluation Periods
                            </h2>

                            <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                {periods.map((p) => (
                                    <div
                                        key={p.name}
                                        className={`rounded-xl border p-4 ${
                                            p.enabled
                                                ? "border-blue-200 bg-blue-50/40 dark:border-blue-900/40 dark:bg-blue-950/20"
                                                : "border-gray-200 bg-gray-50/50 opacity-60 dark:border-gray-800 dark:bg-gray-900/30"
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <p className="text-xs font-bold uppercase text-gray-800 dark:text-gray-200">
                                                {p.name}
                                            </p>
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-[10px] font-bold ${
                                                    p.enabled
                                                        ? "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300"
                                                        : "bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                                }`}
                                            >
                                                {p.enabled ? "Configured" : "Not Set"}
                                            </span>
                                        </div>

                                        {p.enabled ? (
                                            <div className="mt-3 space-y-1 text-xs">
                                                <p className="text-gray-600 dark:text-gray-300">
                                                    <span className="font-semibold text-gray-800 dark:text-gray-200">
                                                        Start:
                                                    </span>{" "}
                                                    {p.period_start}
                                                </p>
                                                <p className="text-gray-600 dark:text-gray-300">
                                                    <span className="font-semibold text-gray-800 dark:text-gray-200">
                                                        End:
                                                    </span>{" "}
                                                    {p.period_end}
                                                </p>
                                                {p.description && (
                                                    <p className="mt-1 text-gray-500 italic dark:text-gray-400 truncate">
                                                        "{p.description}"
                                                    </p>
                                                )}
                                            </div>
                                        ) : (
                                            <p className="mt-3 text-xs text-gray-400 dark:text-gray-500">
                                                To be determined later
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Submission Actions */}
                        <div className="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <Button variant="secondary" onClick={handlePrev}>
                                ← Back: Edit Periods
                            </Button>

                            <div className="flex items-center gap-3">
                                <Button
                                    variant="secondary"
                                    onClick={() => router.visit("/semesters")}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    onClick={handleSubmit}
                                    disabled={updateMutation.isPending}
                                    className="inline-flex items-center gap-2"
                                >
                                    {updateMutation.isPending ? (
                                        <>
                                            <ArrowPathIcon className="h-4 w-4 animate-spin" />
                                            Saving Changes...
                                        </>
                                    ) : (
                                        <>
                                            <CheckCircleIcon className="h-4 w-4" />
                                            Save Semester Changes
                                        </>
                                    )}
                                </Button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

Edit.layout = (page) => <MainLayout>{page}</MainLayout>;
