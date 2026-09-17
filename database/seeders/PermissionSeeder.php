<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Student Management
            ['permission_name' => 'students.view', 'description' => 'View student list and profile details'],
            ['permission_name' => 'students.create', 'description' => 'Register individual and bulk students'],
            ['permission_name' => 'students.edit', 'description' => 'Edit student information and enrollments'],
            ['permission_name' => 'students.archive', 'description' => 'Archive and restore student accounts'],
            ['permission_name' => 'students.reset_password', 'description' => 'Reset student passwords to default'],

            // Instructor Management
            ['permission_name' => 'instructors.view', 'description' => 'View instructor list and profile details'],
            ['permission_name' => 'instructors.create', 'description' => 'Register new instructors'],
            ['permission_name' => 'instructors.edit', 'description' => 'Edit instructor information and department'],
            ['permission_name' => 'instructors.archive', 'description' => 'Archive and restore instructor accounts'],
            ['permission_name' => 'instructors.reset_password', 'description' => 'Reset instructor passwords to default'],

            // Roles & RBAC Management
            ['permission_name' => 'roles.view', 'description' => 'View system roles and permission matrix'],
            ['permission_name' => 'roles.manage', 'description' => 'Customize and update role permissions'],

            // Academic Structure
            ['permission_name' => 'departments.manage', 'description' => 'Manage departments and academic units'],
            ['permission_name' => 'programs.manage', 'description' => 'Manage academic degree programs'],
            ['permission_name' => 'semesters.manage', 'description' => 'Manage school years and active semesters'],
            ['permission_name' => 'courses.manage', 'description' => 'Manage course catalog and course blocks'],
            ['permission_name' => 'facilities.manage', 'description' => 'Manage buildings, rooms, and BLE beacons'],
            ['permission_name' => 'schedules.manage', 'description' => 'Manage class schedules and room assignments'],

            // Attendance & Sessions
            ['permission_name' => 'attendance.sessions.manage', 'description' => 'Start, stop, and control attendance sessions'],
            ['permission_name' => 'attendance.records.view', 'description' => 'View attendance logs and session records'],
            ['permission_name' => 'attendance.records.edit', 'description' => 'Manually adjust or override attendance records'],

            // System Audit
            ['permission_name' => 'audit.view', 'description' => 'View system audit logs and activity history'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['permission_name' => $perm['permission_name']],
                ['description' => $perm['description']]
            );
        }
    }
}
