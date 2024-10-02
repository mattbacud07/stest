<?php

namespace App\Http\Middleware;

use App\Models\RoleUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module, $action): Response
    {
        $user = Auth::user();

        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roleUsers = RoleUser::leftjoin('roles', 'roles.roleID', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('role_user.*', 'roles.role_name', 'roles.roleID', 'roles.permissions') // Select the fields you need
            ->get();

        if(!$roleUsers){
            return response()->json(['error' => 'No roles found'], 403);
        }

        // $permissions = json_decode($roleUser->permissions, true);
        foreach($roleUsers as $roleUser){
            $permissions = json_decode($roleUser['permissions'], true);
            if($permissions){
                foreach($permissions as $permission){
                    if ($permission['module'] === $module && isset($permission[$action]) && $permission[$action]) {
                        return $next($request);
                    }
                }
            }
        }
        
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
