<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Semester;
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
            'semester_end' => '2026-12-30'
        ]);
    }
}
