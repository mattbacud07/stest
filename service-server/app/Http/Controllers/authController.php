<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class authController extends Controller
{
    public function dashboardLogin(Request $request)
    {
        try {
            $isLogin = false;
            $users = $request->only('email', 'password');
            if (Auth::guard('usersSecond')->attempt($users)) {
                $user = Auth::guard('usersSecond')->user();
                if ($user && $user->status === 1) {
                    $isLogin = true;
                    /** @var \App\Models\authLogin\UserModel $user * */
                    // Create token
                    $token = $user->createToken($user->email);

                    /** Get Complete User Details available for all users*/
                    $userInformation = DB::table(DB::connection('mysqlSecond')->getDatabaseName().'.users as u')
                        ->select('p.position_name' , 'd.name as department_name')
                        ->join(DB::connection('mysqlSecond')->getDatabaseName().'.positions as p','u.position','=','p.id')
                        ->join(DB::connection('mysqlSecond')->getDatabaseName().'.departments as d','u.department','=','d.id')
                        ->where('u.id',$user->id)->get();
                    //     select('
                    //     SELECT u.id, u.first_name , u.last_name, p.position_name, d.name as department_name
                    //     FROM '.DB::connection('mysqlSecond')->getDatabaseName().'.users u
                    //     JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.positions p ON u.position = p.id
                    //     JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.departments d ON u.department = d.id
                    //     WHERE u.id = '.$user->id.'
                    // ');

                    
                    /** Get Approval Config details */
                    $getApprovalConfig = DB::table('approval_configuration')->select('approval_level')->where('user_id',$user->id)->get();
                    // , a.approval_level
                    // JOIN '.DB::connection('mysql')->getDatabaseName().'.approval_configuration a ON a.user_id = u.id

                    /** Get user role */
                    $getRole = DB::table('roles')->select('role_name')->where('user_id', $user->id)->pluck('role_name')->toArray();
                    // , r.role_name
                    // JOIN '.DB::connection('mysql')->getDatabaseName().'.roles r ON u.id = r.user_id

                    $userData = [];
                    /** Approval Config */
                    if(isset($getApprovalConfig[0]) && !empty($getApprovalConfig[0])){
                        $userApprovalConfig = (array)$getApprovalConfig[0];
                    }else{
                        $userApprovalConfig = [];
                    }

                    /** Roles */
                    if(isset($getRole) && !empty($getRole)){
                        $getUserRole = $getRole;
                    }else{
                        $getUserRole = [];
                    }
                    // if(isset($getRole[0]) && !empty($getRole[0])){
                    //     $getUserRole = (array)$getRole[0];
                    // }else{
                    //     $getUserRole = [];
                    // }
                    
                    // $userData = $userInformation;
                    $userData = array_merge($user->toArray(), (array)$userInformation[0], $userApprovalConfig);
                    $userData['user_roles'] = $getUserRole;

                    return response()->json([
                        'isLogin' => $isLogin,
                        'accessToken' => $token->plainTextToken,
                        'user' => $userData,
                        // 'user_roles' => $getUserRole,
                    ], 200);
                } else {
                    return response()->json([
                        'isLogin' => false,
                        'status' => 'inactive',
                    ], 200);
                }
            } else {
                return response()->json(['isLogin' => $isLogin], 200);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::error($th->getMessage());

            return response()->json(['error' => 'An error occurred.'.$th], 500);
        }
    }

    public function logmeout()
    {
        Auth::guard('usersSecond')->logout();
        Session::invalidate();
        if (!Auth::guard('usersSecond')->check()) {
            return response()->json([
                'isLogout' => true,
            ]);
        } else {
            return response()->json([
                'isLogout' => false,
            ]);
        }
    }

    public function sample()
    {
        return response()->json(['status' => 'OK Found']);
    }
}
