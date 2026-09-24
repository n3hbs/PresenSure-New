<?php

namespace App\Repositories\Interfaces;

interface InstructorRepositoryInterface
{
    public function create(array $data);

    public function getAllInstructors();

    public function getInstructorDetails(string $userId, ?int $semesterId = null);

    public function archiveInstructor(string $userId): bool;

    public function updateInstructor(string $userId, array $data): bool;

    public function restoreInstructor(string $userId): bool;

    public function getArchivedInstructors();
}
