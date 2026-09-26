import { useEffect, useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
    AcademicCapIcon,
    ArrowLeftIcon,
    BuildingOffice2Icon,
    CheckCircleIcon,
    PlusIcon,
    TrashIcon,
} from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import Button from "@/Components/UI/Button";
import Stepper from "@/Components/UI/Stepper";
import DiscardRegistrationModal from "@/Components/UI/DiscardRegistrationModal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import { notify } from "@/Services/toast";
import { departmentsQueryKey } from "@/Services/queryKeys";
import usePermission from "@/Hooks/usePermission";

const steps = [
    { number: 1, label: "Department Details" },
    { number: 2, label: "Degree Programs" },
    { number: 3, label: "Review" },
];

const ReviewItem = ({ label, value }) => (
    <div className="rounded-lg bg-gray-50 px-4 py-3">
        <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">
            {label}
        </p>
        <p className="mt-1 break-words text-sm font-semibold text-gray-900">
            {value || "—"}
        </p>
    </div>
);

const ReviewGroup = ({ title, children }) => (
    <div className="rounded-xl border border-gray-100 bg-white p-4">
        <h2 className="text-sm font-bold uppercase tracking-wide text-gray-500">
            {title}
        </h2>
        <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {children}
        </div>
    </div>
);

export default function Edit() {
    const { can, hasRole } = usePermission();
    const queryClient = useQueryClient();
    const params = new URLSearchParams(window.location.search);
    const departmentId = params.get("department_id");

    const [currentStep, setCurrentStep] = useState(1);

    // Permission guard
    useEffect(() => {
        if (!hasRole("administrator") && !can("departments.manage")) {
            notify.error("Access Denied", "You do not have permission to edit departments.");
            router.visit("/departments");
        }
    }, [can, hasRole]);

    useEffect(() => {
        if (!departmentId) {
            notify.warning("Missing Department ID", "Please select a department to edit.");
            router.visit("/departments");
        }
    }, [departmentId]);

    // Fetch existing department data
    const {
        data: department,
        isLoading,
        isError,
    } = useQuery({
        queryKey: ["department-details", departmentId],
        queryFn: async () => {
            const token = getAuthToken();
            const res = await api.get(`/v1/departments/${departmentId}`, {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return res.data?.data;
        },
        enabled: Boolean(departmentId) && Boolean(getAuthToken()),
    });

    const [form, setForm] = useState({
        department_code: "",
        department_name: "",
        description: "",
    });

    const [programs, setPrograms] = useState([]);
    const [errors, setErrors] = useState({});
    const [isDiscardModalOpen, setIsDiscardModalOpen] = useState(false);

    // Populate form when department data arrives
    useEffect(() => {
        if (department) {
            setForm({
                department_code: department.department_code || "",
                department_name: department.department_name || "",
                description: department.description || "",
            });

            if (department.programs && department.programs.length > 0) {
                setPrograms(
                    department.programs.map((p) => ({
                        program_id: p.program_id,
                        program_code: p.program_code,
                        program_name: p.program_name,
                        program_years: p.program_years || 4,
                    }))
                );
            } else {
                setPrograms([{ program_code: "", program_name: "", program_years: 4 }]);
            }
        }
    }, [department]);

    const handleFieldChange = (field, value) => {
        setForm((prev) => ({ ...prev, [field]: value }));
        if (errors[field]) {
            setErrors((prev) => {
                const next = { ...prev };
                delete next[field];
                return next;
            });
        }
    };

    const handleProgramChange = (index, field, value) => {
        setPrograms((prev) => {
            const next = [...prev];
            next[index] = { ...next[index], [field]: value };
            return next;
        });

        const errorKey = `programs.${index}.${field}`;
        if (errors[errorKey]) {
            setErrors((prev) => {
                const next = { ...prev };
                delete next[errorKey];
                return next;
            });
        }
    };

    const handleAddProgram = () => {
        setPrograms((prev) => [
            ...prev,
            { program_code: "", program_name: "", program_years: 4 },
        ]);
    };

    const handleRemoveProgram = (index) => {
        setPrograms((prev) => prev.filter((_, idx) => idx !== index));
    };

    // Client validation
    const validateStep1 = () => {
        const errs = {};
        if (!form.department_code.trim()) {
            errs.department_code = "Department code is required.";
        }
        if (!form.department_name.trim()) {
            errs.department_name = "Department name is required.";
        }
        setErrors(errs);
        return Object.keys(errs).length === 0;
    };

    const validateStep2 = () => {
        const errs = {};
        const seenCodes = new Set();

        programs.forEach((prog, idx) => {
            const hasCode = Boolean(prog.program_code.trim());
            const hasName = Boolean(prog.program_name.trim());

            if (hasCode && !hasName) {
                errs[`programs.${idx}.program_name`] = "Program name is required.";
            } else if (!hasCode && hasName) {
                errs[`programs.${idx}.program_code`] = "Program code is required.";
            }

            if (hasCode) {
                const upperCode = prog.program_code.trim().toUpperCase();
                if (seenCodes.has(upperCode)) {
                    errs[`programs.${idx}.program_code`] = `Duplicate program code '${upperCode}'.`;
                }
                seenCodes.add(upperCode);
            }
        });

        setErrors(errs);
        return Object.keys(errs).length === 0;
    };

    const handleNext = () => {
        if (currentStep === 1 && validateStep1()) {
            setCurrentStep(2);
        } else if (currentStep === 2 && validateStep2()) {
            setCurrentStep(3);
        }
    };

    const handleBack = () => {
        if (currentStep > 1) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    // Update Mutation
    const updateMutation = useMutation({
        mutationFn: async (payload) => {
            const token = getAuthToken();
            const res = await api.put(`/v1/departments/${departmentId}`, payload, {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return res.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: departmentsQueryKey });
            queryClient.invalidateQueries({ queryKey: ["department-details", departmentId] });
            notify.success("Department Updated", "Department has been updated successfully.");
            router.visit(`/departments/department-details?department_id=${departmentId}`);
        },
        onError: (err) => {
            const serverErrors = err.response?.data?.data?.errors || err.response?.data?.errors || {};
            setErrors(serverErrors);

            const firstMsg =
                Object.values(serverErrors)[0]?.[0] ||
                err.response?.data?.message ||
                "Failed to update department. Please review inputs.";

            notify.error("Update Failed", firstMsg);

            if (serverErrors.department_code || serverErrors.department_name) {
                setCurrentStep(1);
            } else if (Object.keys(serverErrors).some((k) => k.startsWith("programs"))) {
                setCurrentStep(2);
            }
        },
    });

    const handleSubmit = () => {
        if (!validateStep1() || !validateStep2()) return;

        const validPrograms = programs
            .filter((p) => p.program_code.trim() && p.program_name.trim())
            .map((p) => ({
                program_id: p.program_id || null,
                program_code: p.program_code.trim().toUpperCase(),
                program_name: p.program_name.trim(),
                program_years: parseInt(p.program_years, 10) || 4,
            }));

        const payload = {
            department_code: form.department_code.trim().toUpperCase(),
            department_name: form.department_name.trim(),
            description: form.description.trim() || null,
            programs: validPrograms,
        };

        updateMutation.mutate(payload);
    };

    const activeProgramsList = useMemo(() => {
        return programs.filter((p) => p.program_code.trim() && p.program_name.trim());
    }, [programs]);

    return (
        <div className="space-y-6">
            <Head title={department ? `Edit ${department.department_name}` : "Edit Department"} />

            {/* Header: Breadcrumbs & Cancel */}
            <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <Breadcrumbs
                        crumbs={[
                            { label: "Dashboard", href: "/dashboard" },
                            { label: "Departments", href: "/departments" },
                            {
                                label: department
                                    ? `Edit ${department.department_code}`
                                    : "Edit Department",
                            },
                        ]}
                    />
                </div>

                <button
                    type="button"
                    onClick={() => router.visit(departmentId ? `/departments/department-details?department_id=${departmentId}` : "/departments")}
                    className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                >
                    <ArrowLeftIcon className="h-4 w-4" />
                    Back to Department Details
                </button>
            </div>

            {/* Main Form Container */}
            <section className="mx-auto max-w-7xl rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm shadow-blue-950/5 sm:p-8">
                {/* Stepper Header */}
                <div className="mb-8">
                    <Stepper steps={steps} currentStep={currentStep} />
                </div>

                {isLoading ? (
                    <div className="space-y-4 animate-pulse">
                        <div className="h-8 w-64 rounded bg-gray-100" />
                        <div className="h-12 w-full rounded bg-gray-100" />
                        <div className="h-28 w-full rounded bg-gray-100" />
                    </div>
                ) : (
                    <>
                        {/* Step 1: Department Information */}
                        {currentStep === 1 && (
                            <div className="space-y-6">
                                <div className="border-b border-gray-100 pb-4">
                                    <h2 className="text-lg font-bold text-gray-900">
                                        Edit Department Details
                                    </h2>
                                    <p className="text-xs text-gray-400">
                                        Update code and official name for this academic department.
                                    </p>
                                </div>

                                <div className="grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                                            Department Code <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={form.department_code}
                                            onChange={(e) =>
                                                handleFieldChange("department_code", e.target.value.toUpperCase())
                                            }
                                            placeholder="e.g., CCS"
                                            className={`h-11 w-full rounded-xl border bg-gray-50 px-4 text-sm font-medium transition focus:bg-white focus:outline-none focus:ring-2 ${
                                                errors.department_code
                                                    ? "border-red-300 focus:ring-red-200"
                                                    : "border-gray-200 focus:border-blue-600 focus:ring-blue-100"
                                            }`}
                                        />
                                        {errors.department_code && (
                                            <p className="mt-1 text-xs text-red-600">
                                                {Array.isArray(errors.department_code) ? errors.department_code[0] : errors.department_code}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                                            Department Name <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={form.department_name}
                                            onChange={(e) =>
                                                handleFieldChange("department_name", e.target.value)
                                            }
                                            placeholder="e.g., College of Computer Studies"
                                            className={`h-11 w-full rounded-xl border bg-gray-50 px-4 text-sm font-medium transition focus:bg-white focus:outline-none focus:ring-2 ${
                                                errors.department_name
                                                    ? "border-red-300 focus:ring-red-200"
                                                    : "border-gray-200 focus:border-blue-600 focus:ring-blue-100"
                                            }`}
                                        />
                                        {errors.department_name && (
                                            <p className="mt-1 text-xs text-red-600">
                                                {Array.isArray(errors.department_name) ? errors.department_name[0] : errors.department_name}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                                        Description / Remarks (Optional)
                                    </label>
                                    <textarea
                                        rows={4}
                                        value={form.description}
                                        onChange={(e) => handleFieldChange("description", e.target.value)}
                                        placeholder="Add descriptive notes..."
                                        className="w-full rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm transition focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    />
                                </div>

                                <div className="flex justify-end pt-4 border-t border-gray-100">
                                    <Button type="button" onClick={handleNext}>
                                        Next: Degree Programs
                                    </Button>
                                </div>
                            </div>
                        )}

                        {/* Step 2: Degree Programs */}
                        {currentStep === 2 && (
                            <div className="space-y-6">
                                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4">
                                    <div>
                                        <h2 className="text-lg font-bold text-gray-900">
                                            Manage Degree Programs
                                        </h2>
                                        <p className="text-xs text-gray-400">
                                            Modify existing programs or attach new programs to this department.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={handleAddProgram}
                                        className="mt-2 sm:mt-0 inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 transition"
                                    >
                                        <PlusIcon className="h-4 w-4" />
                                        Add Program
                                    </button>
                                </div>

                                <div className="space-y-4">
                                    {programs.map((program, index) => (
                                        <div
                                            key={index}
                                            className="relative rounded-xl border border-gray-200/80 bg-gray-50/50 p-5 transition hover:border-blue-200"
                                        >
                                            <div className="flex items-center justify-between mb-3">
                                                <span className="text-xs font-bold uppercase tracking-wider text-gray-500">
                                                    Program #{index + 1} {program.program_id && "(Existing)"}
                                                </span>
                                                {programs.length > 1 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRemoveProgram(index)}
                                                        className="rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                                        title="Remove this program"
                                                    >
                                                        <TrashIcon className="h-4 w-4" />
                                                    </button>
                                                )}
                                            </div>

                                            <div className="grid gap-4 sm:grid-cols-12">
                                                <div className="sm:col-span-3">
                                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                                        Program Code
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={program.program_code}
                                                        onChange={(e) =>
                                                            handleProgramChange(index, "program_code", e.target.value.toUpperCase())
                                                        }
                                                        placeholder="e.g., BSIT"
                                                        className={`h-10 w-full rounded-lg border bg-white px-3 text-sm transition focus:outline-none focus:ring-2 ${
                                                            errors[`programs.${index}.program_code`]
                                                                ? "border-red-300 focus:ring-red-200"
                                                                : "border-gray-200 focus:border-blue-600 focus:ring-blue-100"
                                                        }`}
                                                    />
                                                    {errors[`programs.${index}.program_code`] && (
                                                        <p className="mt-1 text-xs text-red-600">
                                                            {errors[`programs.${index}.program_code`]}
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="sm:col-span-6">
                                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                                        Program Name
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={program.program_name}
                                                        onChange={(e) =>
                                                            handleProgramChange(index, "program_name", e.target.value)
                                                        }
                                                        placeholder="e.g., Bachelor of Science in Information Technology"
                                                        className={`h-10 w-full rounded-lg border bg-white px-3 text-sm transition focus:outline-none focus:ring-2 ${
                                                            errors[`programs.${index}.program_name`]
                                                                ? "border-red-300 focus:ring-red-200"
                                                                : "border-gray-200 focus:border-blue-600 focus:ring-blue-100"
                                                        }`}
                                                    />
                                                    {errors[`programs.${index}.program_name`] && (
                                                        <p className="mt-1 text-xs text-red-600">
                                                            {errors[`programs.${index}.program_name`]}
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="sm:col-span-3">
                                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                                        Program Years
                                                    </label>
                                                    <select
                                                        value={program.program_years}
                                                        onChange={(e) =>
                                                            handleProgramChange(index, "program_years", e.target.value)
                                                        }
                                                        className="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                                    >
                                                        <option value={2}>2 Years</option>
                                                        <option value={3}>3 Years</option>
                                                        <option value={4}>4 Years</option>
                                                        <option value={5}>5 Years</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Button variant="secondary" onClick={handleBack}>
                                        Back
                                    </Button>
                                    <Button type="button" onClick={handleNext}>
                                        Next: Review Changes
                                    </Button>
                                </div>
                            </div>
                        )}

                        {/* Step 3: Review */}
                        {currentStep === 3 && (
                            <div className="space-y-6">
                                <div className="border-b border-gray-100 pb-4">
                                    <h2 className="text-lg font-bold text-gray-900">
                                        Review Department Changes
                                    </h2>
                                    <p className="text-xs text-gray-400">
                                        Please review the updated department information before saving.
                                    </p>
                                </div>

                                <div className="space-y-4">
                                    <ReviewGroup title="Department Overview">
                                        <ReviewItem label="Department Code" value={form.department_code} />
                                        <ReviewItem label="Department Name" value={form.department_name} />
                                        <ReviewItem label="Description" value={form.description || "None specified"} />
                                    </ReviewGroup>

                                    <div className="rounded-xl border border-gray-100 bg-white p-4">
                                        <h3 className="text-sm font-bold uppercase tracking-wide text-gray-500 mb-3">
                                            Offered Degree Programs ({activeProgramsList.length})
                                        </h3>

                                        {activeProgramsList.length > 0 ? (
                                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                {activeProgramsList.map((prog, idx) => (
                                                    <div
                                                        key={idx}
                                                        className="rounded-lg border border-gray-100 bg-gray-50 p-3.5"
                                                    >
                                                        <span className="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">
                                                            {prog.program_code}
                                                        </span>
                                                        <p className="mt-1 text-sm font-bold text-gray-900">
                                                            {prog.program_name}
                                                        </p>
                                                        <p className="text-xs text-gray-400 mt-0.5">
                                                            Duration: {prog.program_years} Years
                                                        </p>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-xs text-gray-400 italic">
                                                No degree programs configured.
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Button
                                        variant="secondary"
                                        onClick={handleBack}
                                        disabled={updateMutation.isPending}
                                    >
                                        Back
                                    </Button>
                                    <Button
                                        type="button"
                                        onClick={handleSubmit}
                                        loading={updateMutation.isPending}
                                    >
                                        Update Department
                                    </Button>
                                </div>
                            </div>
                        )}
                    </>
                )}
            </section>
        </div>
    );
}

Edit.layout = (page) => <MainLayout>{page}</MainLayout>;
