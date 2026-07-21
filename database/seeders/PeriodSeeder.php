<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semester = Semester::where('term', 'First Semester')->firstOrFail();

        $periods = [
            [
                'name' => 'prelim',
                'description' => 'Preliminary Period',
                'period_start' => '2026-06-17',
                'period_end' => '2026-08-15',
            ],
            [
                'name' => 'midterm',
                'description' => 'Midterm Period',
                'period_start' => '2026-08-16',
                'period_end' => '2026-10-15',
            ],
            [
                'name' => 'prefinals',
                'description' => 'Pre-finals Period',
                'period_start' => '2026-10-16',
                'period_end' => '2026-11-30',
            ],
            [
                'name' => 'finals',
                'description' => 'Final Period',
                'period_start' => '2026-12-01',
                'period_end' => '2026-12-30',
            ],
        ];

        foreach ($periods as $period) {
            Period::updateOrCreate(
                [
                    'semester_id' => $semester->semester_id,
                    'name' => $period['name'],
                ],
                [
                    'description' => $period['description'],
                    'period_start' => $period['period_start'],
                    'period_end' => $period['period_end'],
                ]
            );
        }
    }
}
