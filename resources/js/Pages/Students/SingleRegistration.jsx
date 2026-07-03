import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { ArrowLeftIcon } from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import SingleRegistrationForm from "@/Components/Students/Register/SingleRegistrationForm";
import StudentRegistrationReview from "@/Components/Students/Register/StudentRegistrationReview";
import StudentRegistrationStepper from "@/Components/Students/Register/StudentRegistrationStepper";
import StudentRegistrationToast from "@/Components/Students/Register/StudentRegistrationToast";
import StudentTypeStep from "@/Components/Students/Register/StudentTypeStep";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DiscardRegistrationModal from "@/Components/UI/DiscardRegistrationModal";
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

export default function SingleRegistration() {
    const queryClient = useQueryClient();
    const allowNavigationRef = useRef(false);
    const [currentStep, setCurrentStep] = useState(1);
    const [registrationType, setRegistrationType] = useState("");
    const [existingUserId, setExistingUserId] = useState("");
    const [checkingStudent, setCheckingStudent] = useState(false);
    const [form, setForm] = useState(emptyForm);
    const [image, setImage] = useState(null);
    const [imagePreview, setImagePreview] = useState("");
    const [submitting, setSubmitting] = useState(false);
    const [fieldErrors, setFieldErrors] = useState({});
    const [toast, setToast] = useState(null);
    const [confirmDiscardOpen, setConfirmDiscardOpen] = useState(false);
    const [pendingNavigationUrl, setPendingNavigationUrl] = useState(null);

    const showToast = useCallback((type, title, message = "") => {
        setToast({ type, title, message, id: Date.now() });
    }, []);

    const isDirty = useMemo(
        () =>
            currentStep > 1 ||
            Boolean(registrationType) ||
            Object.values(form).some((value) => String(value || "").trim()) ||
            Boolean(image),
        [currentStep, form, image, registrationType],
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
        const getPathFromUrl = (url) => {
            if (!url) return null;

            try {
                const parsedUrl =
                    url instanceof URL
                        ? url
                        : new URL(String(url), window.location.origin);

                return `${parsedUrl.pathname}${parsedUrl.search}${parsedUrl.hash}`;
            } catch {
                return String(url);
            }
        };

        const removeBeforeListener = router.on("before", (event) => {
            if (!isDirty) return undefined;

            if (allowNavigationRef.current) {
                allowNavigationRef.current = false;
                return undefined;
            }

            const targetUrl = getPathFromUrl(event.detail.visit.url);
            const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;

            if (!targetUrl || targetUrl === currentUrl) return undefined;

            setPendingNavigationUrl(targetUrl);
            setConfirmDiscardOpen(true);

            return false;
        });

        return () => removeBeforeListener();
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

    const selectedDepartment = useMemo(
        () =>
            departmentOptions.find(
                (department) => department.value === String(form.department_id),
            ),
        [departmentOptions, form.department_id],
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

    const selectedProgramOption = useMemo(
        () =>
            programOptions.find(
                (program) => program.value === String(form.program_id),
            ),
        [form.program_id, programOptions],
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

    const resetRegistration = () => {
        setCurrentStep(1);
        setRegistrationType("");
        setExistingUserId("");
        setForm(emptyForm);
        setImage(null);
        setFieldErrors({});
    };

    const handleExistingUserIdChange = (event) => {
        setExistingUserId(makeStudentId(event.target.value));
    };

    const selectNewStudent = () => {
        setRegistrationType("new");
        setForm(emptyForm);
        setImage(null);
        setFieldErrors({});
        setCurrentStep(2);
    };

    const checkExistingStudent = async () => {
        if (!existingUserId) {
            showToast(
                "warning",
                "Student number required",
                "Enter a student number before checking.",
            );
            return;
        }

        setCheckingStudent(true);

        try {
            const response = await api.get(`/student/check-user/${existingUserId}`, {
                headers: getAuthHeaders(),
            });
            const result = response.data;

            if (!result.exists) {
                showToast(
                    "error",
                    "Student not found",
                    result.message || "No account found for that student number.",
                );
                return;
            }

            if (result.already_enrolled) {
                showToast(
                    "warning",
                    "Already enrolled",
                    result.message ||
                        "This student is already enrolled in the active semester.",
                );
                return;
            }

            setRegistrationType("existing");
            setImage(null);
            setFieldErrors({});
            setForm({
                ...emptyForm,
                user_id: result.data?.user_id || existingUserId,
                first_name: result.data?.first_name || "",
                middle_initial: result.data?.middle_initial || "",
                last_name: result.data?.last_name || "",
                suffix: result.data?.suffix || "",
                sex: result.data?.sex || "",
            });
            setCurrentStep(2);
            showToast("success", "Student found", result.message);
        } catch (requestError) {
            showToast(
                "error",
                "Unable to check student",
                requestError.response?.data?.message ||
                    "Please try checking the student number again.",
            );
        } finally {
            setCheckingStudent(false);
        }
    };

    const handleChange = (event) => {
        const { name, value } = event.target;

        if (registrationType === "existing") return;

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
        const requiredFields =
            registrationType === "existing"
                ? ["user_id", "department_id", "program_id", "year", "block"]
                : [
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

    const continueToReview = (event) => {
        event.preventDefault();

        if (!validate()) {
            showToast(
                "warning",
                "Missing required fields",
                "Please fill in the highlighted fields before reviewing.",
            );
            return;
        }

        setCurrentStep(3);
    };

    const submitRegistration = async () => {
        if (!validate()) {
            setCurrentStep(2);
            showToast(
                "warning",
                "Missing required fields",
                "Please fill in the highlighted fields before registering.",
            );
            return;
        }

        setSubmitting(true);

        const payload = new FormData();
        payload.append("registration_type", registrationType);

        const payloadFields =
            registrationType === "existing"
                ? ["user_id", "program_id", "year", "block"]
                : Object.keys(form).filter((key) => key !== "department_id");

        payloadFields.forEach((key) => {
            payload.append(key, form[key] || "");
        });

        if (registrationType === "new" && image) {
            payload.append("image", image);
        }

        try {
            const response = await api.post(
                "/student/single-registration",
                payload,
                {
                    headers: {
                        ...getAuthHeaders(),
                        "Content-Type": "multipart/form-data",
                    },
                },
            );

            showToast(
                "success",
                "Registration complete",
                response.data?.message || "Student registered successfully.",
            );
            resetRegistration();
            queryClient.invalidateQueries({
                queryKey: activeStudentsQueryKey,
            });
        } catch (requestError) {
            if (requestError.response?.status === 422) {
                setFieldErrors(requestError.response.data.errors || {});
                setCurrentStep(2);
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

    const requestPage = (url) => {
        if (isDirty) {
            setPendingNavigationUrl(url);
            setConfirmDiscardOpen(true);
            return;
        }

        router.visit(url);
    };

    const discardAndLeave = () => {
        const targetUrl = pendingNavigationUrl || "/students";

        setConfirmDiscardOpen(false);
        setPendingNavigationUrl(null);
        allowNavigationRef.current = true;
        router.visit(targetUrl);
    };

    const keepEditing = () => {
        setConfirmDiscardOpen(false);
        setPendingNavigationUrl(null);
    };

    return (
        <>
            <Head title="Single Registration" />
            <StudentRegistrationToast
                toast={toast}
                onClose={() => setToast(null)}
            />
            <DiscardRegistrationModal
                open={confirmDiscardOpen}
                onKeepEditing={keepEditing}
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
                        onClick={() => requestPage("/students")}
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Students
                    </button>
                </div>

                <StudentRegistrationStepper currentStep={currentStep} />

                {currentStep === 1 && (
                    <StudentTypeStep
                        existingUserId={existingUserId}
                        checkingStudent={checkingStudent}
                        onExistingUserIdChange={handleExistingUserIdChange}
                        onSelectNew={selectNewStudent}
                        onCheckExisting={checkExistingStudent}
                    />
                )}

                {currentStep === 2 && (
                    <SingleRegistrationForm
                        form={form}
                        image={image}
                        imagePreview={imagePreview}
                        fieldErrors={fieldErrors}
                        sexOptions={sexOptions}
                        departmentOptions={departmentOptions}
                        programOptions={programOptions}
                        yearOptions={yearOptions}
                        blockOptions={blockOptions}
                        loadingOptions={loadingOptions}
                        registrationType={registrationType}
                        onSubmit={continueToReview}
                        onTextChange={handleChange}
                        onSelectChange={updateSelect}
                        onImageChange={handleImageChange}
                        onRemoveImage={() => setImage(null)}
                        onBack={() => setCurrentStep(1)}
                        onCancel={() => requestPage("/students")}
                    />
                )}

                {currentStep === 3 && (
                    <StudentRegistrationReview
                        form={form}
                        imagePreview={imagePreview}
                        registrationType={registrationType}
                        selectedDepartment={selectedDepartment}
                        selectedProgram={selectedProgramOption}
                        submitting={submitting}
                        onBack={() => setCurrentStep(2)}
                        onSubmit={submitRegistration}
                    />
                )}
            </div>
        </>
    );
}

SingleRegistration.layout = (page) => <MainLayout>{page}</MainLayout>;
