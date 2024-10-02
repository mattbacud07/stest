<?php

namespace App\Http\Middleware;

use App\Models\Roles;
use App\Models\RoleUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminRoleID = Roles::adminRoleID;
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roleUsers = RoleUser::leftjoin('roles', 'roles.roleID', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->where('role_user.role_id', $adminRoleID)
            ->select('role_user.role_id') 
            ->first();
        if(!$roleUsers) return response()->json(['error' => 'Forbidden'], 403); 

        return $next($request);
    }
}
