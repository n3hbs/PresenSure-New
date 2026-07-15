<?php

namespace App\Services;

use App\Repositories\ProgramRepository;

class ProgramService
{
    public function __construct(
        protected ProgramRepository $programRepository
    ) {}

    public function getPrograms()
    {
        return $this->programRepository->getAll();
    }
}
