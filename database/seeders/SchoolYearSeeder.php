<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchoolYear::create([
            'school_year_start' => '2026-06-17',
            'school_year_end' => '2027-05-30',
        ]);
    }
}
