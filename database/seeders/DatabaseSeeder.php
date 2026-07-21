<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Independent user data
            UserSeeder::class,

            // Academic calendar
            SchoolYearSeeder::class,
            SemesterSeeder::class,
            PeriodSeeder::class,

            // Organization
            DepartmentSeeder::class,
            ProgramSeeder::class,

            // Authorization
            RoleSeeder::class,
            UserRoleSeeder::class,

            // Locations
            BuildingSeeder::class,
            RoomSeeder::class,
            BleDeviceSeeder::class,

            // Courses
            CourseSeeder::class,
            CourseBlockSeeder::class,

            // User assignments
            UserCourseBlockSeeder::class,

            // Class schedules
            ScheduleSeeder::class,
            ScheduleDaysSeeder::class,
        ]);
    }
}
