<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\Scope;

class RoleScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $assignments = [
        'student' => ['profile:read', 'student:profile:read', 'student:status:read', 'student:schedule:read', 'student:subjects:read', 'library:books:read', 'library:loans:create', 'library:loans:read', 'library:loans:renew', 'library:fines:read', 'library:fines:pay', 'support:tickets:create', 'support:tickets:read', 'support:tickets:comment','files:read','files:upload'],
        'teacher' => ['profile:read', 'student:profile:read', 'student:status:read', 'library:books:read', 'library:loans:create', 'library:loans:read', 'support:tickets:create', 'support:tickets:read','files:read','files:upload'],
        'librarian' => ['profile:read', 'library:books:read', 'library:loans:read', 'library:loans:renew', 'library:fines:read', 'library:fines:pay', 'library:inventory:manage','files:read','files:upload'],
        'support_agent' => ['profile:read', 'support:tickets:read', 'support:tickets:comment', 'support:tickets:assign', 'support:tickets:close','files:read','files:upload'],
        'academic_admin' => ['profile:read', 'student:profile:read', 'student:status:read', 'student:schedule:read', 'student:subjects:read','files:read','files:upload','files:delete'],
        'security_admin' => ['profile:read', 'audit:events:read','user:security_status:read','files:read','files:upload','files:delete'],
    ];

    foreach ($assignments as $roleName => $scopeNames) {
        $role = Role::where('name', $roleName)->first();
        $scopes = Scope::whereIn('name', $scopeNames)->get();
        $role->scopes()->attach($scopes->pluck('id'));
    }

    // super_admin: todos los scopes sin excepción
    $superAdmin = Role::where('name', 'super_admin')->first();
    $superAdmin->scopes()->attach(Scope::all()->pluck('id'));
}
}
