<?php

namespace App\Services;

use App\Repositories\InstructorRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstructorService
{
    public function __construct(
        protected UserService $userService,
        protected UserProfileService $userProfileService,
        protected InstructorRepository $instructorRepository,
        protected RoleService $roleService,
        protected SemesterService $semesterService,
        protected UserRepositoryInterface $userRepository,
    ) {}
    public function createInstructor(array $data)
    {
        return DB::transaction(function () use ($data) {

            //create User
            $user = $this->userService
                ->createUser($data);

            //upload and store user image
            $profile = $this->userProfileService
                ->uploadProfile($data['image'] ?? null, $data['user_id']);

            //register instructor
            $instructor = $this->instructorRepository->create([
                'user_id' => $data['user_id'],
                'department_id' => $data['department_id'],
                'status' => 'Active'
            ]);

            //get role_id by role_name
            $role_id = $this->roleService->getRoleId('instructor');

            if (!$role_id) {
                throw ValidationException::withMessages([
                    'role_id' => [
                        'Instructor role not found.'
                    ],
                ]);
            }

            //assign user role
            $this->roleService->assignUserRole($data['user_id'], $role_id);

            return $instructor;
        });
    }

    public function getAllInstructors()
    {
        return $this->instructorRepository->getAllInstructors();
    }

    public function getInstructorDetails(string $userId)
    {
        $semester = $this->semesterService->getActiveSemester();
        $semesterId = $semester?->semester_id;

        $instructor = $this->instructorRepository->getInstructorDetails($userId, $semesterId);

        if (!$instructor) {
            throw ValidationException::withMessages([
                'user_id' => ['Instructor not found.'],
            ]);
        }

        return $instructor;
    }

    public function updateInstructor(string $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = $this->userRepository->findByUserId($userId);
            if (!$user) {
                throw ValidationException::withMessages([
                    'user_id' => ['Instructor user account not found.'],
                ]);
            }

            $userFields = [];
            if (isset($data['first_name'])) $userFields['first_name'] = $data['first_name'];
            if (isset($data['last_name'])) $userFields['last_name'] = ucfirst(strtolower($data['last_name']));
            if (array_key_exists('middle_initial', $data)) $userFields['middle_initial'] = $data['middle_initial'];
            if (array_key_exists('suffix', $data)) $userFields['suffix'] = $data['suffix'];
            if (isset($data['sex'])) $userFields['sex'] = $data['sex'];

            if (!empty($userFields)) {
                $this->userRepository->update($userId, $userFields);
            }

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $this->userProfileService->uploadProfile($data['image'], $userId);
            }

            $instructorFields = [];
            if (isset($data['department_id'])) $instructorFields['department_id'] = $data['department_id'];
            if (isset($data['status'])) $instructorFields['status'] = $data['status'];

            if (!empty($instructorFields)) {
                $this->instructorRepository->updateInstructor($userId, $instructorFields);
            }

            return $this->getInstructorDetails($userId);
        });
    }

    public function archiveInstructor(string $userId): bool
    {
        return $this->instructorRepository->archiveInstructor($userId);
    }

    public function deleteInstructor(string $userId): bool
    {
        return $this->instructorRepository->archiveInstructor($userId);
    }

    public function restoreInstructor(string $userId): bool
    {
        return $this->instructorRepository->restoreInstructor($userId);
    }

    public function getArchivedInstructors()
    {
        return $this->instructorRepository->getArchivedInstructors();
    }
}
