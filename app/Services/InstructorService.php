<?php

namespace App\Services;

use App\Repositories\InstructorRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstructorService
{
    public function __construct(
        protected UserService $userService,
        protected UserProfileService $userProfileService,
        protected InstructorRepository $instructorRepository,
        protected RoleService $roleService
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
}
