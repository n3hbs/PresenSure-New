import { BookOpenIcon } from "@heroicons/react/24/outline";

export default function StudentEnrolledCourses({ courses = [] }) {
    return (
        <section className="rounded-xl bg-white p-6 shadow-sm shadow-blue-950/5">
            <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                    <BookOpenIcon className="h-5 w-5" />
                </div>
                <h2 className="text-lg font-semibold text-gray-900">
                    Enrolled Courses
                </h2>
            </div>

            {courses.length > 0 ? (
                <div className="mt-5 grid gap-4">
                    {courses.map((course) => (
                        <div
                            key={course.course_id}
                            className="rounded-lg border border-gray-200 bg-white p-4"
                        >
                            <p className="font-semibold text-gray-900">
                                {course.subject_code || "Course"}
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                {course.description || "No description"}
                            </p>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="mt-5 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-10 text-center">
                    <p className="text-sm font-medium text-gray-500">
                        No enrolled course details are available for this
                        student yet.
                    </p>
                </div>
            )}
        </section>
    );
}
