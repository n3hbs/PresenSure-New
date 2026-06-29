<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create([
            'department_code' => 'CTE',
            'department_name' => 'College of Teacher Education',
        ]);

        Department::create([
            'department_code' => 'CBE',
            'department_name' => 'College of Business Education',
        ]);
    }
}
