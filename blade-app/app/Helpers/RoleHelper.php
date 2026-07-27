<?php

namespace App\Helpers;

class RoleHelper{

    public static function hasRole(string $role):bool{
        return in_array($role, session('roles',[]),true);
    }

    public static function hasAnyRole(array $roles):bool{
        return array_intersect($roles, session('roles',[])) !== [];
    }

    public static function isAgent():bool{
        return  self::hasAnyRole(['support_agent','security_admin','academic_admin','super_admin']);
    }

    public static function isLibrarian():bool{
        return self::hasRole('librarian');
    }

    public static function isSecurityAdmin():bool{
        return self::hasRole('security_admin');
    }

}