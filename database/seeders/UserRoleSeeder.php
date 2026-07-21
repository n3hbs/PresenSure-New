<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = [
            [
                'user_id' => '2000-0001',
                'role_name' => 'administrator',
            ],
            [
                'user_id' => 'C-2022-0138',
                'role_name' => 'student',
            ],
            [
                'user_id' => '2022-0138',
                'role_name' => 'instructor',
            ],
        ];

        foreach ($assignments as $assignment) {
            $user = User::where(
                'user_id',
                $assignment['user_id']
            )->firstOrFail();

            $role = Role::where(
                'role_name',
                $assignment['role_name']
            )->firstOrFail();

            UserRole::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'role_id' => $role->role_id,
                ],
                [
                    'assigned_at' => now(),
                ]
            );
        }
    }
}
