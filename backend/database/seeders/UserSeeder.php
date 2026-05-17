<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed test users for each role.
     */
    public function run(): void
    {
        $investorRole = Role::where('name', 'Investor')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $auditorRole = Role::where('name', 'Auditor')->first();

        User::updateOrCreate(
            ['email' => 'investor@test.com'],
            [
                'name' => 'Test Investor',
                'password' => Hash::make('password123'),
                'role_id' => $investorRole->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'auditor@test.com'],
            [
                'name' => 'Test Auditor',
                'password' => Hash::make('password123'),
                'role_id' => $auditorRole->id,
            ]
        );
    }
}
