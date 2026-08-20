<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Administrator System',
        //     'email' => 'admin@test.com',
        //     'password' => Hash::make('123456'),
        //     'role' => 'admin',
        //     'email_verified_at' => now(),
        // ]);

        // 2. Organizer
        User::create([
            'name' => 'Event Creator',
            'email' => 'creator@test.com',
            'password' => Hash::make('123456'),
            'role' => 'creator',
            'email_verified_at' => now(),
        ]);

        // 3. Donatur
        User::create([
            'name' => 'Donatur Peduli',
            'email' => 'backer@test.com',
            'password' => Hash::make('123456'),
            'role' => 'backer',
            'email_verified_at' => now(),
        ]);
    }
}