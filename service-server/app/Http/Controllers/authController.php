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

                    /** Get Complete User Datails */
                    $userInformation = DB::select('
                        SELECT u.id, u.first_name , u.last_name, p.position_name, d.name as department_name, r.role_name
                        FROM '.DB::connection('mysqlSecond')->getDatabaseName().'.users u
                        JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.positions p ON u.position = p.id
                        JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.departments d ON u.department = d.id
                        JOIN '.DB::connection('mysql')->getDatabaseName().'.roles r ON u.id = r.user_id
                        WHERE u.id = '.$user->id.'
                    ');
                    $userData = [];
                    if(isset($userInformation[0]) && !empty($userInformation[0])){
                        $userInfo = (array)$userInformation[0];
                        $userData = array_merge($user->toArray(), $userInfo);
                    }else{
                        $userData = $user;
                    }

                    return response()->json([
                        'isLogin' => $isLogin,
                        'accessToken' => $token->plainTextToken,
                        'user' => $userData,
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
            Log::error($th);

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
