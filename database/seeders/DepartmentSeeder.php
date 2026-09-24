<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

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
