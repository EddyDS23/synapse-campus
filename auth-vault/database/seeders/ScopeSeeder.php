<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Scope;

class ScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $scopes_data  = [
            'profile:read',
            'student:profile:read',
            'student:status:read',
            'student:schedule:read',
            'student:subjects:read',
            'library:books:read',
            'library:loans:create',
            'library:loans:read',
            'library:loans:renew',
            'library:fines:read',
            'library:fines:pay',
            'library:inventory:manage',
            'support:tickets:create',
            'support:tickets:read',
            'support:tickets:comment',
            'support:tickets:assign',
            'support:tickets:close',
            'audit:events:write',
            'audit:events:read',
            'internal:security_status:read',
            'internal:student-status:read',
            'user:security_status:read'
        ];

        foreach($scopes_data as $scope_name){
            Scope::create(['name'=>"{$scope_name}"]);
        }
    }
}
