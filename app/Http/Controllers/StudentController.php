<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\CreateStudentRequest;
use App\Http\Requests\Student\ExtractBulkStudentRequest;
use App\Http\Requests\Student\StoreBulkStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Services\StudentService;
use App\Http\Resources\Student\ActiveSemesterStudentListResource;
use App\Http\Resources\Student\CheckStudentResource;
use App\Http\Resources\Student\StudentDetailsResource;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
    ) {}

    public function create(CreateStudentRequest $request)
    {
        $student = $this->studentService->registerStudent($request->validated());

        return $this->successResponse(
            new StudentResource($student),
            'Student registered successfully.',
            201
        );
    }

    public function getStudentByActiveSemester()
    {
        $students = $this->studentService->getStudentByActiveSemester();
        return ActiveSemesterStudentListResource::collection($students)
            ->message('Student List Retrieved Successfully.')
            ->status(200);
    }

    public function getStudentDetails(string $user_id)
    {
        $student = $this->studentService->getStudentDetails($user_id);
        return (new StudentDetailsResource($student))
            ->message('Student Details Retrieved Successfully.')
            ->status(200);
    }

    public function checkStudent(string $user_id)
    {
        $student = $this->studentService->checkStudent($user_id);
        return (new CheckStudentResource($student))
            ->message('Student Checked Successfully.')
            ->status(200);
    }

    public function extractBulk(ExtractBulkStudentRequest $request)
    {
        try {
            $data = $this->studentService->extractBulkStudents($request->file('file'));
            return $this->successResponse($data, 'Spreadsheet extracted successfully.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function storeBulk(StoreBulkStudentRequest $request)
    {
        try {
            $result = $this->studentService->storeBulkStudents($request->validated('students'));
            return $this->successResponse($result, 'Bulk students registered successfully.', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="PresenSure_Student_Template.csv"',
        ];

        $columns = ['Student ID', 'Full Name', 'Gender', 'Program', 'Year Level', 'Block'];
        $samples = [
            ['C-2027-0001', 'Dela Cruz, Juan A. Jr.', 'Male', 'BSIT', 'First Year', 'A'],
            ['C-2027-0002', 'Santos, Maria B.', 'Female', 'BSIT', 'First Year', 'B'],
            ['C-2027-0003', 'Reyes, Carlo M.', 'Male', 'BSIT', 'Second Year', 'A'],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($samples as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function update(UpdateStudentRequest $request, ?string $user_id = null)
    {
        $userId = $user_id ?? $request->input('user_id');
        if (!$userId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => ['The student ID (user_id) is required.'],
            ]);
        }

        $student = $this->studentService->updateStudent($userId, $request->validated());

        return (new StudentDetailsResource($student))
            ->message('Student updated successfully.')
            ->status(200);
    }

    public function archive(string $user_id)
    {
        $this->studentService->archiveStudent($user_id);

        return $this->successResponse(
            null,
            'Student archived successfully.',
            200
        );
    }

    public function delete(Request $request, string $user_id)
    {
        $permanent = filter_var($request->query('permanent', false), FILTER_VALIDATE_BOOLEAN);
        $this->studentService->deleteStudent($user_id, $permanent);

        return $this->successResponse(
            null,
            $permanent ? 'Student deleted successfully.' : 'Student archived successfully.',
            200
        );
    }

    public function getArchivedStudents()
    {
        $students = $this->studentService->getArchivedStudents();
        return ActiveSemesterStudentListResource::collection($students)
            ->message('Archived Students Retrieved Successfully.')
            ->status(200);
    }

    public function restore(string $user_id)
    {
        $this->studentService->restoreStudent($user_id);

        return $this->successResponse(
            null,
            'Student restored successfully.',
            200
        );
    }
}


