<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRoleTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'student',
            'teacher',
            'librarian',
            'support_agent',
            'academic_admin',
            'security_admin',
            'super_admin',
        ];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            $user = User::create([
                'name' => ucfirst($roleName),
                'email' => "{$roleName}@test.com",
                'password' => 'Password123!',
            ]);

            $user->roles()->attach($role->id);
        }
    }
}
