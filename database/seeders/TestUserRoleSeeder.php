<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds for testing each role and its login redirect.
     *
     * @return void
     */
    public function run()
    {
        $commonPassword = Hash::make('password123');

        // 1. Define Roles and their associated permissions
        $roles = [
            'admin' => [
                'name' => 'System Administrator',
                'permissions' => ['*'],
            ],
            'it-support' => [
                'name' => 'Developer / IT',
                'permissions' => [
                    'devices.view',
                    'devices.create',
                    'devices.edit',
                    'devices.delete',
                    'cameras.view',
                    'cameras.manage',
                    'security.view',
                    'audit_logs.view',
                ],
            ],
            'receptionist' => [
                'name' => 'Front Desk Receptionist',
                'permissions' => [
                    'visitors.view',
                    'visitors.manage',
                ],
            ],
            'security' => [
                'name' => 'Security Guard',
                'permissions' => [
                    'visitors.view',
                    'cameras.view',
                    'security.view',
                ],
            ],
            'employee' => [
                'name' => 'Regular Employee',
                'permissions' => [
                    'attendance.view',
                ],
            ],
        ];

        $roleIds = [];
        foreach ($roles as $slug => $data) {
            $existingRole = DB::table('roles')->where('slug', $slug)->first();
            if ($existingRole) {
                DB::table('roles')->where('id', $existingRole->id)->update([
                    'name' => $data['name'],
                    'permissions' => json_encode($data['permissions']),
                    'updated_at' => now(),
                ]);
                $roleIds[$slug] = $existingRole->id;
            } else {
                $roleIds[$slug] = DB::table('roles')->insertGetId([
                    'slug' => $slug,
                    'name' => $data['name'],
                    'permissions' => json_encode($data['permissions']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Define Test Users for each role
        $testUsers = [
            [
                'name' => 'Admin Test User',
                'email' => 'admin@ams.com',
                'role' => 'admin',
                'position' => 'System Administrator',
            ],
            [
                'name' => 'IT Support User',
                'email' => 'itsupport@ams.com',
                'role' => 'it-support',
                'position' => 'IT Support Engineer',
            ],
            [
                'name' => 'Receptionist User',
                'email' => 'receptionist@ams.com',
                'role' => 'receptionist',
                'position' => 'Front Desk Executive',
            ],
            [
                'name' => 'Security User',
                'email' => 'security@ams.com',
                'role' => 'security',
                'position' => 'Security Guard',
            ],
            [
                'name' => 'Employee User',
                'email' => 'employee@ams.com',
                'role' => 'employee',
                'position' => 'Staff Member',
            ],
        ];

        foreach ($testUsers as $userData) {
            // Find or create User record
            $user = DB::table('users')->where('email', $userData['email'])->first();

            if ($user) {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => $userData['name'],
                    'password' => $commonPassword,
                    'role' => $userData['role'],
                    'status' => 'Active',
                    'failed_logins' => 0,
                    'locked_until' => null,
                    'updated_at' => now(),
                ]);
                $userId = $user->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $commonPassword,
                    'role' => $userData['role'],
                    'status' => 'Active',
                    'failed_logins' => 0,
                    'locked_until' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Attach / Update role in role_users pivot table
            $targetRoleId = $roleIds[$userData['role']];
            
            // Remove any outdated role mapping for this test user
            DB::table('role_users')->where('user_id', $userId)->delete();

            // Insert role link
            DB::table('role_users')->insert([
                'user_id' => $userId,
                'role_id' => $targetRoleId,
            ]);

            // Ensure matching record exists in employees table for roster / attendance purposes
            $employee = DB::table('employees')->where('email', $userData['email'])->first();
            if (!$employee) {
                DB::table('employees')->insert([
                    'name' => $userData['name'],
                    'position' => $userData['position'],
                    'email' => $userData['email'],
                    'pin_code' => '123',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Test users and roles created/updated successfully!');
    }
}
