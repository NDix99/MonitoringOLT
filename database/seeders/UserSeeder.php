<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@oltmonitoring.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create regular user
        User::create([
            'name' => 'Operator',
            'email' => 'operator@oltmonitoring.com',
            'password' => Hash::make('operator123'),
            'role' => 'user',
            'is_active' => true,
        ]);
    }
}
