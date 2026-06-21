<?php

namespace App\Repositories\Interfaces;

interface StudentRepositoryInterface
{
    public function create(array $data);
    public function isEnrolled(string $userId, int $semesterId): bool;
    public function getStudentByActiveSemester(int $semeesterId);
}
