<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
    'name' => 'Investor',
    'description' => 'User with investment privileges to track and manage investments',
    'created_at' => now(),
    'updated_at' => now(),
    ]);
    DB::table('roles')->insert([
    'name' => 'Admin',
    'description' => 'Administrator with full access',
    'created_at' => now(),
    'updated_at' => now(),
    ]);
    DB::table('roles')->insert([
    'name' => 'Auditor',
    'description' => 'User with auditing privileges',
    'created_at' => now(),
    'updated_at' => now(),
    ]);
    }
}
