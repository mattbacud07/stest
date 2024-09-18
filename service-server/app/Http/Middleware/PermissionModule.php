<?php

namespace App\Http\Middleware;

use App\Models\RoleUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, Guard $guard): Response
    {
        $user = $guard->user();

        $roleUser = RoleUser::where('user_id', $user->id)->first();

        if(!$roleUser){
            return response()->json(['error' => 'Unauthorized: No role assigned'], 403);
        }

        $role = $roleUser->role_id;
        return $next($request);
    }
}
