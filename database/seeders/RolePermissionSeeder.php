<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('role_name', 'administrator')->first();
        $instructorRole = Role::where('role_name', 'instructor')->first();
        $studentRole = Role::where('role_name', 'student')->first();

        $allPermissions = Permission::all();

        // 1. Administrator gets all permissions and is marked as system admin
        if ($adminRole) {
            $adminRole->update(['is_system_admin' => true]);
            $adminRole->permissions()->sync($allPermissions->pluck('permission_id')->toArray());
        }

        // 2. Instructor permissions
        if ($instructorRole) {
            $instructorPermNames = [
                'students.view',
                'attendance.sessions.manage',
                'attendance.records.view',
                'attendance.records.edit',
                'courses.manage',
                'schedules.manage',
            ];

            $instructorPermIds = Permission::whereIn('permission_name', $instructorPermNames)
                ->pluck('permission_id')
                ->toArray();

            $instructorRole->permissions()->sync($instructorPermIds);
        }

        // 3. Student permissions
        if ($studentRole) {
            $studentPermNames = [
                'attendance.records.view',
            ];

            $studentPermIds = Permission::whereIn('permission_name', $studentPermNames)
                ->pluck('permission_id')
                ->toArray();

            $studentRole->permissions()->sync($studentPermIds);
        }
    }
}
