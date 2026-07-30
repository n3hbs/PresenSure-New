<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\StudentRepository;
use Exception;
use Illuminate\Support\Facades\DB;

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
            $semester = $this->semesterService->getActiveSemester()['data'] ?? null;

            // check if there is an active semester
            if (! $semester) {
                throw new Exception('No active semester found.');
            }

            // check if the student already enrolled
            if ($this->studentRepository->isEnrolled($data['user_id'], $semester->semester_id)) {
                throw new Exception('Student already enrolled.');
            }

            // register student
            $student = $this->studentRepository->create([
                'user_id' => $data['user_id'],
                'semester_id' => $semester->semester_id,
                'program_id' => $data['program_id'],
                'year' => $data['year'],
                'block' => $data['block'],
            ]);
        });
    }

    public function getStudentByActiveSemester()
    {
        $semester = $this->semesterService->getActiveSemester()['data'] ?? null;
        return $this->studentRepository->getStudentByActiveSemester($semester->semester_id);
    }

    public function getStudentDetails(string $user_id)
    {
        $semester = $this->semesterService->getActiveSemester()['data'] ?? null;
        return $this->studentRepository->getStudentDetails($user_id, $semester->semester_id);
    }

    public function checkStudent(string $user_id)
    {
        $semester = $this->semesterService->getActiveSemester()['data'] ?? null;
        if (!$semester) {
            throw new Exception('No active semester found.');
        }

        $user = $this->userRepository->findByUserId($user_id);

        if (!$user) {
            return [
                'exists' => false,
                'already_enrolled' => false,
                'message' => 'Student account not found.',
                'user' => null,
            ];
        }

        $alreadyEnrolled = $this->studentRepository->isEnrolled(
            $user_id,
            $semester->semester_id
        );

        return [
            'exists' => true,
            'already_enrolled' => $alreadyEnrolled,
            'message' => $alreadyEnrolled
                ? 'Student is already enrolled in the active semester.'
                : 'Student account found.',
            'user' => $user,
        ];
    }
}
