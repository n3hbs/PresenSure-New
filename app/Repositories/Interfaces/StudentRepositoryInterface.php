<?php

namespace App\Repositories\Interfaces;

interface StudentRepositoryInterface
{
    public function create(array $data);
    public function isEnrolled(string $userId, int $semesterId): bool;
    public function getStudentByActiveSemester(int $semeesterId);
    public function getStudentDetails(string $user_id, int $semeesterId);
    public function updateStudent(string $userId, int $semesterId, array $data);
    public function archiveStudent(string $userId, int $semesterId): bool;
    public function deleteStudent(string $userId, ?int $semesterId = null): bool;
    public function getArchivedStudents(?int $semesterId = null);
    public function restoreStudent(string $userId, ?int $semesterId = null): bool;
}


