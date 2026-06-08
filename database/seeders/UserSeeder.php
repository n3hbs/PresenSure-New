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
        User::create([
            'user_id' => '2000-0001',
            'first_name' => 'Juan',
            'middle_initial' => 'D',
            'last_name' => 'Dela Cruz',
            'password' => Hash::make('administrator'),
        ]);
    }
}
