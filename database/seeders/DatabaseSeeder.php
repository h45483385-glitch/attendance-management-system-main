<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create the Admin Role
        $roleId = DB::table('roles')->insertGetId([
            'slug' => 'admin',
            'name' => 'System Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create the User Account
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@ams.com',
            'password' => Hash::make('admin@ams.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Build the Bridge: Attach the Role to the User
        DB::table('role_users')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        // 4. Create the linked Employee Profile
        DB::table('employees')->insert([
            'name' => 'Admin User',
            'position' => 'Administrator',
            'email' => 'admin@ams.com',
            'pin_code' => '123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}