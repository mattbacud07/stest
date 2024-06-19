<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MainDashboardController extends Controller
{
    public function index(){
        // Get the  role of the User
        $roles = DB::connection('mysqlSecond')->table('roles')->pluck('role_name','role_id')->toArray();
        
        $current_user_role = Auth::guard('usersSecond')->user()->role;
        $check_role = in_array($current_user_role, array_keys($roles));
        $check_role ?
            $get_user_role_name = $roles[$current_user_role] : $get_user_role_name = null;
            // dd($get_user_role_name);
        return view('dashboard', [
            'user_role_name' => $get_user_role_name,
        ]);
    }
}
