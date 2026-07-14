<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'user_id' => '2000-0001',
                'first_name' => 'Juan',
                'middle_initial' => 'D',
                'last_name' => 'Dela Cruz',
                'sex' => 'male',
                'password' => Hash::make('administrator'),
            ],
            [
                'user_id' => 'C-2022-0138',
                'first_name' => 'Student',
                'middle_initial' => null,
                'last_name' => 'User',
                'sex' => 'male',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['user_id' => $user['user_id']],
                $user
            );
        }
    }
}
