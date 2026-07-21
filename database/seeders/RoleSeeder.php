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
                'description' => 'Administrator account',
            ],
            [
                'role_name' => 'instructor',
                'description' => 'Instructor account',
            ],
            [
                'role_name' => 'student',
                'description' => 'Student account',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'role_name' => $role['role_name'],
                ],
                [
                    'description' => $role['description'],
                ]
            );
        }
    }
}