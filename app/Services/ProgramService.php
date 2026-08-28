<?php

namespace App\Services;

use App\Repositories\ProgramRepository;
use Illuminate\Validation\ValidationException;

class ProgramService
{
    public function __construct(
        protected ProgramRepository $programRepository
    ) {}

    public function getPrograms()
    {
        $program = $this->programRepository->getAll();
        if ($program->isEmpty()) {
            throw ValidationException::withMessages([
                'program_id' => [
                    'No program found.',
                ],
            ]);
        }

        return $program;
    }
}
