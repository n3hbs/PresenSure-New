<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;
class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::create([
            'department_id' => 1,
            'program_code' => 'BSIT',
            'program_name' => 'Bachelor of Science in Information Technology',
            'program_years' => 4
        ]);
    }
}
