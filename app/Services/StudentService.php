<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
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
        private RoleService $roleService
    ) {}

    public function registerStudent(array $data)
    {
        return DB::transaction(function () use ($data) {

            //create User
            $user = $this->userService
                ->createUser($data);

            //upload and store user image
            $profile = $this->userProfileService
                ->uploadProfile($data['image'] ?? null, $data['user_id']);

            //get active semester
            $semester = $this->semesterService
                ->getActiveSemester();


            //check if there is an active semester
            if (!$semester) {
                throw new Exception(
                    'No active semester found.'
                );
            }

            //check if the student already enrolled
            if ($this->studentRepository->isEnrolled($data['user_id'], $semester->semester_id)) {
                throw new Exception(
                    'Student already enrolled.'
                );
            }

            //register student
            $student = $this->studentRepository->create([
                'user_id' => $data['user_id'],
                'semester_id' => $semester->semester_id,
                'program_id' => $data['program_id'],
                'year' => $data['year'],
                'block' => $data['block'],
            ]);

            //get role_id by role_name
            $role_id = $this->roleService->getRoleId('student');

            //assign user role
            $this->roleRepository->assignUserRole($data['user_id'], $role_id);

            return [
                'message' => "Student Successfully Registered"
            ];
        });
    }

    public function getStudentByActiveSemester()
    {
        $semesterId = $this->semesterService->getActiveSemester();
        return $this->studentRepository->getStudentByActiveSemester($semesterId->semester_id);
    }

    public function getStudentDetails(string $user_id)
    {
        $semesterId = $this->semesterService->getActiveSemester();
        return $this->studentRepository->getStudentDetails($user_id,$semesterId->semester_id);
    }
}
