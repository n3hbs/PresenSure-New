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
        $user = User::where('user_id', '2000-0001')->firstOrFail();

        $role = Role::where('role_name', 'administrator')->firstOrFail();

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
