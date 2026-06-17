<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    
    public function assign(Request $request, int $id):JsonResponse{

        $roleName = $request->input('role');

        $role = Role::where('name',$roleName)->first();
        
        if($role == null){
            return response()->json(['message'=>'Role not found'],404);
        }

        $user = User::where('id',$id)->first();

        if($user == null){
            return response()->json(['message'=>'User not found'],404);
        }

        if($user->roles->contains('name',$role->id)){
            return response()->json(['message'=>'User already has that role'],409);
        }
        
        $user->roles()->attach($role->id);

        return response()->json(['message'=>'Updated user role'],200);
    
    }

    public function revoke(int $id, string $role):JsonResponse{
        
        $role = Role::where('name',$role)->first();
        
        if($role == null){
            return response()->json(['message'=>'Role not found'],404);
        }

        $user = User::where('id',$id)->first();

        if($user == null){
            return response()->json(['message'=>'User not found'],404);
        }

        if(!$user->roles->contains('name',$role->id)){
            return response()->json(['message'=>"User hasn't that role"],200);
        }

        $user->roles()->detach($role->id);

        return response()->json(['mesage'=>'Revoke user role'],200);
        
    }

}
