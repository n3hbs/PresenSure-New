<?php

namespace App\Services;

use App\Imports\StudentBulkImport;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\StudentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StudentService
{
    public function __construct(
        private UserService $userService,
        private UserProfileService $userProfileService,
        private SemesterService $semesterService,
        private StudentRepository $studentRepository,
        private RoleRepository $roleRepository,
        private RoleService $roleService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function registerStudent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $isExisting = ($data['registration_type'] ?? 'new') === 'existing';

            if (! $isExisting) {
                // create User
                $user = $this->userService
                    ->createUser($data);

                // upload and store user image
                $profile = $this->userProfileService
                    ->uploadProfile($data['image'] ?? null, $data['user_id']);

                // get role_id by role_name
                $role_id = $this->roleService->getRoleId('student');

                // assign user role
                $this->roleRepository->assignUserRole($data['user_id'], $role_id);
            }

            // get active semester
            $semester = $this->semesterService->getActiveSemester();

            // check if there is an active semester
            if (! $semester) {
                throw ValidationException::withMessages([
                    'semester_id' => [
                        'No active semester found.',
                    ],
                ]);
            }

            // check if the student already enrolled
            if ($this->studentRepository->isEnrolled($data['user_id'], $semester->semester_id)) {
                throw ValidationException::withMessages([
                    'student_id' => [
                        'Student already enrolled.',
                    ],
                ]);
            }

            // register student
            return $this->studentRepository->create([
                'user_id' => $data['user_id'],
                'semester_id' => $semester->semester_id,
                'program_id' => $data['program_id'],
                'year' => $data['year'],
                'block' => $data['block'],
                'status' => 'Active',
            ]);
        });
    }

    public function getStudentByActiveSemester()
    {
        $semester = $this->semesterService->getActiveSemester();

        if (! $semester) {
            throw ValidationException::withMessages([
                'semester_id' => ['No active semester found.'],
            ]);
        }

        return $this->studentRepository->getStudentByActiveSemester($semester->semester_id);
    }

    public function getStudentDetails(string $user_id)
    {
        $semester = $this->semesterService->getActiveSemester();

        if (! $semester) {
            throw ValidationException::withMessages([
                'semester_id' => ['No active semester found.'],
            ]);
        }

        return $this->studentRepository->getStudentDetails($user_id, $semester->semester_id);
    }

    public function checkStudent(string $user_id)
    {
        $semester = $this->semesterService->getActiveSemester();
        if (!$semester) {
            throw ValidationException::withMessages([
                'semester_id' => [
                    'No active semester found.',
                ],
            ]);
        }

        $user = $this->userRepository->findByUserId($user_id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => [
                    'Student account not found.',
                ],
            ]);
        }

        $alreadyEnrolled = $this->studentRepository->isEnrolled(
            $user_id,
            $semester->semester_id
        );

        return [
            'exists' => true,
            'already_enrolled' => $alreadyEnrolled,
            'user' => $user,
        ];
    }

    public function extractBulkStudents(UploadedFile $file): array
    {
        $activeSemester = $this->semesterService->getActiveSemester();
        $import = new StudentBulkImport($activeSemester, $this->studentRepository);

        Excel::import($import, $file);

        return $import->getData();
    }

    public function storeBulkStudents(array $students): array
    {
        return DB::transaction(function () use ($students) {
            $activeSemester = $this->semesterService->getActiveSemester();
            $roleId = $this->roleService->getRoleId('student');

            $enrolledCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($students as $index => $row) {
                $userId = trim($row['user_id'] ?? '');
                if (empty($userId)) {
                    continue;
                }

                try {
                    if ($this->studentRepository->isEnrolled($userId, $activeSemester->semester_id)) {
                        $skippedCount++;
                        continue;
                    }

                    // 1. Create or ensure User exists
                    $user = $this->userService->createUser([
                        'user_id' => $userId,
                        'first_name' => $row['first_name'] ?? '',
                        'last_name' => $row['last_name'] ?? '',
                        'middle_initial' => $row['middle_initial'] ?? null,
                        'suffix' => $row['suffix'] ?? null,
                        'sex' => strtolower($row['sex'] ?? 'male') === 'female' ? 'female' : 'male',
                    ]);

                    // 2. Assign student role
                    $this->roleRepository->assignUserRole($user->user_id, $roleId);

                    // 3. Enroll student for active semester
                    $this->studentRepository->create([
                        'user_id' => $user->user_id,
                        'semester_id' => $activeSemester->semester_id,
                        'program_id' => $row['program_id'],
                        'year' => $row['year'] ?? 'First Year',
                        'block' => $row['block'] ?? 'A',
                        'status' => 'Active',
                    ]);

                    $enrolledCount++;
                } catch (\Throwable $e) {
                    $errors[] = "Row " . ($index + 1) . " ({$userId}): " . $e->getMessage();
                }
            }

            return [
                'enrolled_count' => $enrolledCount,
                'skipped_count' => $skippedCount,
                'total_received' => count($students),
                'errors' => $errors,
            ];
        });
    }
}