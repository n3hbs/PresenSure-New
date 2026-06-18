<?php

namespace App\Services;

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
        private SemesterRepository $semesterRepository,
        private StudentRepository $studentRepository
    ) {}

    public function registerStudent(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = $this->userService
                ->createOrUpdateStudentUser($data);

            $profile = $this->userProfileService
                ->uploadProfile(
                    $data['image'] ?? null,
                    $data['user_id']
                );

            $semester = $this->semesterRepository
                ->getActiveSemester();

            if (!$semester) {
                throw new Exception(
                    'No active semester found.'
                );
            }

            if (
                $this->studentRepository->isEnrolled(
                    $data['user_id'],
                    $semester->semester_id
                )
            ) {
                throw new Exception(
                    'Student already enrolled.'
                );
            }

            return $this->studentRepository->create([
                'user_id'     => $data['user_id'],
                'semester_id' => $semester->semester_id,
                'program_id'     => $data['program_id'],
                'year'        => $data['year'],
                'block'       => $data['block'],
            ]);
        });
    }
}
