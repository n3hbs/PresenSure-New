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
            ['permission_name' => 'students.view', 'module_name' => 'students', 'description' => 'View student list and profile details'],
            ['permission_name' => 'students.create', 'module_name' => 'students', 'description' => 'Register individual and bulk students'],
            ['permission_name' => 'students.edit', 'module_name' => 'students', 'description' => 'Edit student information and enrollments'],
            ['permission_name' => 'students.archive', 'module_name' => 'students', 'description' => 'Archive and restore student accounts'],
            ['permission_name' => 'students.reset_password', 'module_name' => 'students', 'description' => 'Reset student passwords to default'],

            // Instructor Management
            ['permission_name' => 'instructors.view', 'module_name' => 'instructors', 'description' => 'View instructor list and profile details'],
            ['permission_name' => 'instructors.create', 'module_name' => 'instructors', 'description' => 'Register new instructors'],
            ['permission_name' => 'instructors.edit', 'module_name' => 'instructors', 'description' => 'Edit instructor information and department'],
            ['permission_name' => 'instructors.archive', 'module_name' => 'instructors', 'description' => 'Archive and restore instructor accounts'],
            ['permission_name' => 'instructors.reset_password', 'module_name' => 'instructors', 'description' => 'Reset instructor passwords to default'],

            // Roles & RBAC Management
            ['permission_name' => 'roles.view', 'module_name' => 'roles', 'description' => 'View system roles and permission matrix'],
            ['permission_name' => 'roles.manage', 'module_name' => 'roles', 'description' => 'Customize and update role permissions'],

            // Academic Structure
            ['permission_name' => 'departments.manage', 'module_name' => 'academic', 'description' => 'Manage departments and academic units'],
            ['permission_name' => 'programs.manage', 'module_name' => 'academic', 'description' => 'Manage academic degree programs'],
            ['permission_name' => 'semesters.manage', 'module_name' => 'academic', 'description' => 'Manage school years and active semesters'],
            ['permission_name' => 'courses.manage', 'module_name' => 'academic', 'description' => 'Manage course catalog and course blocks'],
            ['permission_name' => 'facilities.manage', 'module_name' => 'academic', 'description' => 'Manage buildings, rooms, and BLE beacons'],
            ['permission_name' => 'schedules.manage', 'module_name' => 'academic', 'description' => 'Manage class schedules and room assignments'],

            // Attendance & Sessions
            ['permission_name' => 'attendance.sessions.manage', 'module_name' => 'attendance', 'description' => 'Start, stop, and control attendance sessions'],
            ['permission_name' => 'attendance.records.view', 'module_name' => 'attendance', 'description' => 'View attendance logs and session records'],
            ['permission_name' => 'attendance.records.edit', 'module_name' => 'attendance', 'description' => 'Manually adjust or override attendance records'],

            // System Audit
            ['permission_name' => 'audit.view', 'module_name' => 'audit', 'description' => 'View system audit logs and activity history'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['permission_name' => $perm['permission_name']],
                [
                    'module_name' => $perm['module_name'],
                    'description' => $perm['description'],
                ]
            );
        }
    }
}
