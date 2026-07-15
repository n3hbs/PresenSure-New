<?php

namespace App\Repositories;

use App\Models\Program;
use App\Repositories\Interfaces\ProgramRepositoryInterface;

class ProgramRepository implements ProgramRepositoryInterface
{
    public function getAll()
    {
        return Program::with('department')
            ->orderBy('program_code')
            ->get();
    }
}
