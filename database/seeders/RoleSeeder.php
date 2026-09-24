<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'administrator',
                'is_system_admin' => true,
                'description' => 'Administrator account',
            ],
            [
                'role_name' => 'instructor',
                'is_system_admin' => false,
                'description' => 'Instructor account',
            ],
            [
                'role_name' => 'student',
                'is_system_admin' => false,
                'description' => 'Student account',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'role_name' => $role['role_name'],
                ],
                [
                    'is_system_admin' => $role['is_system_admin'],
                    'description' => $role['description'],
                ]
            );
        }
    }
}
