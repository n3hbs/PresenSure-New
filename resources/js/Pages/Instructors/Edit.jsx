import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { ArrowLeftIcon, UserCircleIcon } from "@heroicons/react/24/outline";

import MainLayout from "@/Components/Layout/MainLayout";
import InstructorRegistrationReview from "@/Components/Instructors/Register/InstructorRegistrationReview";
import InstructorRegistrationStepper from "@/Components/Instructors/Register/InstructorRegistrationStepper";
import SingleInstructorRegistrationForm from "@/Components/Instructors/Register/SingleInstructorRegistrationForm";
import InstructorDetailsSkeleton from "@/Components/Instructors/Details/InstructorDetailsSkeleton";
import StudentRegistrationToast from "@/Components/Students/Register/StudentRegistrationToast";
import Breadcrumbs from "@/Components/UI/Breadcrumbs";
import DiscardRegistrationModal from "@/Components/UI/DiscardRegistrationModal";
import api from "@/Services/api";
import { getAuthToken } from "@/Services/auth";
import {
    departmentsQueryKey,
    instructorsQueryKey,
} from "@/Services/queryKeys";
import { notify } from "@/Services/toast";
import usePermission from "@/Hooks/usePermission";

const sexOptions = [
    { label: "Male", value: "male" },
    { label: "Female", value: "female" },
];

const editSteps = [
    { number: 1, label: "Information" },
    { number: 2, label: "Review" },
];

const getCollection = (response) => {
    if (Array.isArray(response?.data?.data)) return response.data.data;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

export default function Edit() {
    const { can } = usePermission();
    const queryClient = useQueryClient();
    const params = new URLSearchParams(window.location.search);
    const userId = params.get("user_id");

    useEffect(() => {
        if (!can("instructors.edit")) {
            notify.error(
                "Access Denied",
                "You do not have permission to edit instructor records."
            );
            router.visit("/instructors");
        }
    }, [can]);

    const allowNavigationRef = useRef(false);
    const [currentStep, setCurrentStep] = useState(1);
    const [form, setForm] = useState({
        user_id: "",
        first_name: "",
        middle_initial: "",
        last_name: "",
        suffix: "",
        sex: "",
        department_id: "",
    });

    const [image, setImage] = useState(null);
    const [imagePreview, setImagePreview] = useState("");
    const [submitting, setSubmitting] = useState(false);
    const [fieldErrors, setFieldErrors] = useState({});
    const [toast, setToast] = useState(null);
    const [confirmDiscardOpen, setConfirmDiscardOpen] = useState(false);
    const [pendingNavigationUrl, setPendingNavigationUrl] = useState(null);
    const [isFormInitialized, setIsFormInitialized] = useState(false);

    const showToast = useCallback((type, title, message = "") => {
        setToast({ type, title, message, id: Date.now() });
    }, []);

    const getAuthHeaders = () => {
        const token = getAuthToken();
        return token ? { Authorization: `Bearer ${token}` } : {};
    };

    // Fetch instructor details
    const {
        data: instructorData,
        isLoading: loadingInstructor,
        isError,
        error,
    } = useQuery({
        queryKey: ["instructor-details", userId],
        queryFn: async () => {
            const token = getAuthToken();
            const response = await api.get(`instructor/${userId}`, {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            });
            return response.data.data;
        },
        enabled: Boolean(userId) && Boolean(getAuthToken()),
    });

    // Populate form with existing data
    useEffect(() => {
        if (!instructorData) return;
        const user = instructorData.user || {};
        const instructor = instructorData.instructor || {};
        const profile = instructorData.profile || {};

        setForm({
            user_id: user.user_id || "",
            first_name: user.first_name || "",
            middle_initial: user.middle_initial || "",
            last_name: user.last_name || "",
            suffix: user.suffix || "",
            sex: (user.sex || "").toLowerCase(),
            department_id: instructor.department?.department_id
                ? String(instructor.department.department_id)
                : "",
        });

        if (profile.imagelink) {
            setImagePreview(profile.imagelink);
        }

        setIsFormInitialized(true);
    }, [instructorData]);

    const isUnauthenticated = error?.response?.status === 401;
    const errorMessage = isUnauthenticated
        ? ""
        : error?.response?.data?.message || "Unable to load instructor details.";

    useEffect(() => {
        if (!userId) {
            notify.warning(
                "Missing Instructor ID",
                "Please open an instructor from the instructors list.",
            );
        }
    }, [userId]);

    useEffect(() => {
        if (isError && !isUnauthenticated && errorMessage) {
            notify.error("Unable to Load Instructor", errorMessage);
        }
    }, [isError, isUnauthenticated, errorMessage]);

    // Fetch departments
    const {
        data: departments = [],
        isLoading: loadingDepartments,
    } = useQuery({
        queryKey: departmentsQueryKey,
        enabled: Boolean(getAuthToken()),
        queryFn: async () => {
            const response = await api.get("/departments", {
                headers: getAuthHeaders(),
            });
            return getCollection(response);
        },
    });

    const departmentOptions = useMemo(
        () =>
            departments.map((dept) => ({
                label: dept.department_name,
                value: String(dept.department_id),
            })),
        [departments],
    );

    const selectedDepartment = useMemo(() => {
        const currentDeptId = form.department_id;
        if (!currentDeptId) {
            return {
                label:
                    instructorData?.instructor?.department?.department_name ||
                    "N/A",
                value: "",
            };
        }
        const found = departments.find(
            (dept) => String(dept.department_id) === String(currentDeptId),
        );
        if (found) {
            return {
                label: found.department_name,
                value: String(found.department_id),
            };
        }
        return {
            label:
                instructorData?.instructor?.department?.department_name ||
                "N/A",
            value: String(currentDeptId),
        };
    }, [departments, form.department_id, instructorData]);

    const isDirty = useMemo(() => {
        if (!isFormInitialized || !instructorData) return false;
        const user = instructorData.user || {};
        const instructor = instructorData.instructor || {};

        return (
            form.first_name !== (user.first_name || "") ||
            form.last_name !== (user.last_name || "") ||
            form.middle_initial !== (user.middle_initial || "") ||
            form.suffix !== (user.suffix || "") ||
            form.sex !== ((user.sex || "").toLowerCase()) ||
            String(form.department_id) !== String(instructor.department?.department_id || "") ||
            image !== null
        );
    }, [form, image, isFormInitialized, instructorData]);

    // Warn before unload
    useEffect(() => {
        const handleBeforeUnload = (event) => {
            if (isDirty && !allowNavigationRef.current) {
                event.preventDefault();
                event.returnValue = "";
            }
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () =>
            window.removeEventListener("beforeunload", handleBeforeUnload);
    }, [isDirty]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        const finalValue =
            name === "middle_initial"
                ? value.replace(/[^a-zA-Z]/g, "").toUpperCase().slice(0, 5)
                : value;
        setForm((prev) => ({ ...prev, [name]: finalValue }));

        if (fieldErrors[name]) {
            setFieldErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const updateSelect = (name, value) => {
        setForm((prev) => ({ ...prev, [name]: value }));

        if (fieldErrors[name]) {
            setFieldErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleImageChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        if (!file.type.startsWith("image/")) {
            showToast("error", "Invalid File", "Please select a valid image file.");
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showToast("error", "File Too Large", "Image size must be less than 2MB.");
            return;
        }

        setImage(file);
        setImagePreview(URL.createObjectURL(file));
        if (fieldErrors.image) {
            setFieldErrors((prev) => ({ ...prev, image: null }));
        }
    };

    const validate = () => {
        const errors = {};
        if (!form.first_name?.trim()) errors.first_name = ["First name is required."];
        if (!form.last_name?.trim()) errors.last_name = ["Last name is required."];
        if (!form.sex) errors.sex = ["Please select sex."];
        if (!form.department_id) errors.department_id = ["Please select a department."];

        setFieldErrors(errors);
        return Object.keys(errors).length === 0;
    };

    const continueToReview = (e) => {
        e.preventDefault();
        if (!validate()) {
            showToast(
                "warning",
                "Missing Required Fields",
                "Please fill in all highlighted fields before reviewing.",
            );
            return;
        }
        setCurrentStep(2);
    };

    const submitEdit = async () => {
        if (!validate()) {
            setCurrentStep(1);
            return;
        }

        setSubmitting(true);
        try {
            const payload = new FormData();
            payload.append("_method", "PATCH");
            payload.append("user_id", userId || form.user_id);
            payload.append("first_name", form.first_name);
            payload.append("last_name", form.last_name);
            if (form.middle_initial) payload.append("middle_initial", form.middle_initial);
            if (form.suffix) payload.append("suffix", form.suffix);
            payload.append("sex", form.sex);
            payload.append("department_id", form.department_id);

            if (image) {
                payload.append("image", image);
            }

            const response = await api.post(`instructor/${userId}`, payload, {
                headers: {
                    ...getAuthHeaders(),
                    "Content-Type": "multipart/form-data",
                },
            });

            notify.success(
                "Instructor Updated",
                response.data?.message || "Instructor details updated successfully.",
            );

            queryClient.invalidateQueries({
                queryKey: ["instructor-details", userId],
            });
            queryClient.invalidateQueries({
                queryKey: instructorsQueryKey,
            });

            allowNavigationRef.current = true;
            router.visit(`/instructors/instructor-details?user_id=${userId}`);
        } catch (error) {
            if (error.response?.status === 422) {
                const errors = error.response.data?.errors || {};
                setFieldErrors(errors);
                setCurrentStep(1);
                showToast(
                    "error",
                    "Validation Error",
                    "Please check highlighted fields and try again.",
                );
            } else {
                const message =
                    error.response?.data?.message ||
                    "Failed to update instructor. Please try again.";
                showToast("error", "Update Failed", message);
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
        const targetUrl =
            pendingNavigationUrl ||
            `/instructors/instructor-details?user_id=${userId}`;
        setConfirmDiscardOpen(false);
        setPendingNavigationUrl(null);
        allowNavigationRef.current = true;
        router.visit(targetUrl);
    };

    const user = instructorData?.user || {};
    const fullName = [
        user.first_name,
        user.middle_initial,
        user.last_name,
        user.suffix,
    ]
        .filter(Boolean)
        .join(" ");

    return (
        <>
            <Head title={fullName ? `Edit Instructor - ${fullName}` : "Edit Instructor"} />
            <StudentRegistrationToast
                toast={toast}
                onClose={() => setToast(null)}
            />
            <DiscardRegistrationModal
                open={confirmDiscardOpen}
                onKeepEditing={() => setConfirmDiscardOpen(false)}
                onDiscard={discardAndLeave}
            />

            <div className="space-y-6">
                <div className="flex min-h-10 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Breadcrumbs
                            crumbs={[
                                { label: "Dashboard", href: "/dashboard" },
                                { label: "Instructors", href: "/instructors" },
                                {
                                    label: userId || "Instructor Details",
                                    href: `/instructors/instructor-details?user_id=${userId}`,
                                },
                                { label: "Edit Instructor" },
                            ]}
                        />
                    </div>

                    <button
                        type="button"
                        onClick={() =>
                            requestPage(
                                `/instructors/instructor-details?user_id=${userId}`,
                            )
                        }
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-gray-600 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-700"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Back to Instructor Details
                    </button>
                </div>

                {loadingInstructor ? (
                    <InstructorDetailsSkeleton />
                ) : !instructorData ? (
                    <section className="rounded-xl bg-white p-8 text-center shadow-sm shadow-blue-950/5">
                        <UserCircleIcon className="mx-auto h-12 w-12 text-gray-300" />
                        <p className="mt-3 text-sm font-semibold text-gray-700">
                            Instructor not found.
                        </p>
                        <button
                            type="button"
                            onClick={() => router.visit("/instructors")}
                            className="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >
                            Return to Instructors
                        </button>
                    </section>
                ) : (
                    <>
                        <InstructorRegistrationStepper
                            currentStep={currentStep}
                            steps={editSteps}
                            onStepClick={(stepNumber) => {
                                if (stepNumber < currentStep) {
                                    setCurrentStep(stepNumber);
                                }
                            }}
                        />

                        {currentStep === 1 && (
                            <SingleInstructorRegistrationForm
                                form={form}
                                image={image}
                                imagePreview={imagePreview}
                                fieldErrors={fieldErrors}
                                sexOptions={sexOptions}
                                departmentOptions={departmentOptions}
                                loadingDepartments={loadingDepartments}
                                registrationType="edit"
                                onSubmit={continueToReview}
                                onTextChange={handleChange}
                                onSelectChange={updateSelect}
                                onImageChange={handleImageChange}
                                onRemoveImage={() => {
                                    setImage(null);
                                    setImagePreview("");
                                }}
                                onCancel={() =>
                                    requestPage(
                                        `/instructors/instructor-details?user_id=${userId}`,
                                    )
                                }
                            />
                        )}

                        {currentStep === 2 && (
                            <InstructorRegistrationReview
                                form={form}
                                imagePreview={imagePreview}
                                selectedDepartment={selectedDepartment}
                                submitting={submitting}
                                onBack={() => setCurrentStep(1)}
                                onSubmit={submitEdit}
                            />
                        )}
                    </>
                )}
            </div>
        </>
    );
}

Edit.layout = (page) => <MainLayout>{page}</MainLayout>;
