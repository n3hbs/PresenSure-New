<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Semester::create([
            'school_year_id' => 1,
            'term' => 'First Semester',
            'semester_start' => '2026-06-17',
            'semester_end' => '2026-12-30',
        ]);
    }
}
