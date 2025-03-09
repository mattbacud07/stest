<?php

namespace App\Http\Controllers;

use App\Models\EhServicesModel;
use App\Models\RoleUser;
use Illuminate\Contracts\Auth\Guard;
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
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $users = $request->only('email', 'password');
            if (Auth::guard('usersSecond')->attempt($users)) {
                $user = Auth::guard('usersSecond')->user();
                if ($user && $user->status === 1) {
                    $isLogin = true;
                    /** @var \App\Models\authLogin\UserModel $user * */
                    // Create token
                    $token = $user->createToken($user->email);

                    /** Get Complete User Details available for all users*/
                    $userInformation = DB::table(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u')
                        ->select('p.position_name', 'd.name as department_name')
                        ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.positions as p', 'u.position', '=', 'p.id')
                        ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.departments as d', 'u.department', '=', 'd.id')
                        ->where('u.id', $user->id)->get();


                    /** Get Approval Config details [Approver Category - Equipment Handling Installation] */
                    $getApprovalConfigEHInstallation = DB::table('approval_configuration')
                        ->select('approval_level')
                        ->where('user_id', $user->id)
                        ->where('approver_category', EhServicesModel::EH_INSTALLATION)
                        ->get();

                    /** Get Approval Config details [Approver Category - Equipment Handling Pullout] */
                    $getApprovalConfigEHPullout = DB::table('approval_configuration')
                        ->select('approval_level')
                        ->where('user_id', $user->id)
                        ->where('approver_category', EhServicesModel::EH_PULLOUT)
                        ->get();

                    /** Get user role */
                    $getRole = DB::table('role_user as rU')
                        ->select('role_id', 'r.role_name', 'r.permissions', 'SBU', 'satellite')
                        ->join('roles as r', 'rU.role_id', '=', 'r.roleID')
                        ->where('user_id', $user->id)->get();
                    // ->pluck('r.role_id')
                    // ->toArray();

                    $userData = [];

                    /** Approval Config - Installation */
                    if (isset($getApprovalConfigEHInstallation) && !empty($getApprovalConfigEHInstallation)) {
                        $userApprovalConfigEHInstallation = $getApprovalConfigEHInstallation->pluck('approval_level');
                    } else {
                        $userApprovalConfigEHInstallation = [];
                    }

                    /** Approval Config - Pullout */
                    if (isset($getApprovalConfigEHPullout) && !empty($getApprovalConfigEHPullout)) {
                        $userApprovalConfigEHPullout = $getApprovalConfigEHPullout->pluck('approval_level');
                    } else {
                        $userApprovalConfigEHPullout = [];
                    }

                    /** Roles */
                    if (isset($getRole) && !empty($getRole)) {
                        $getUserRole = $getRole;
                    } else {
                        $getUserRole = [];
                    }

                    // $userData = $userInformation;
                    $userData = array_merge($user->toArray(), (array)$userInformation[0]);
                    $userData['user_roles'] = $getUserRole;
                    $userData['approval_level'] = $userApprovalConfigEHInstallation;
                    $userData['approval_level_pullout'] = $userApprovalConfigEHPullout;

                    return response()->json([
                        'isLogin' => $isLogin,
                        'accessToken' => $token->plainTextToken,
                        'user' => $userData,
                        // 'user_roles' => $getUserRole,
                    ], 200)->cookie(
                        'token',
                        $token->plainTextToken,
                        60 * 24 * 7, // Expiration time in minutes (e.g., 7 days)
                        '/', // Path
                        null, // Domain (null for current domain)
                        true, // Secure (HTTPS only, set to `false` for local development)
                        true // HttpOnly
                    );
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

            return response()->json(['error' => 'An error occurred.' . $th->getMessage()], 500);
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

    /** Get Current Auth User */
    public function get_role_permissions(Request $request)
    {
        // $userID = $guard->user();
        $user = Auth::user();

        $data = RoleUser::select('role_user.*', 'r.roleID', 'r.role_name', 'r.permissions')
            ->where('user_id', $user->id)
            ->leftJoin('roles as r', 'r.roleID', '=', 'role_user.role_id');

        if ($request->has('currentRole') && !empty($request->currentRole)) {
            $currentRole = $request->currentRole;
            $data->where('role_id', $currentRole);
        }

        $userRoles = $data->get();


        // $userDataWithRoles = ['roles' => $userRoles];

        return response()->json([
            'user_roles_permission' => $userRoles,
        ]);
    }



    /** Get the assigned Roles to all users */
    public function get_user_assigned_roles(Request $request)
    {
        $role_id = $request->role;
        $user_role = RoleUser::with(['users' => function ($q) {
            $q->select(
                'users.*',
                'd.name',
                'p.position_name',
                DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullname")
            )
                // ->leftJoin(DB::connection('mysql')->getDatabaseName() . '.approval_configuration as a_p', 'users.id', '=', 'a_p.user_id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.departments as d', 'users.department', '=', 'd.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.positions as p', 'users.position', '=', 'p.id');
        }])
            ->with('roles')
            ->with('approver');

        if ($request->has('role') && !empty($role_id)) {
            $user_role->where('role_id', $role_id);
        }

        $role = $user_role->get();

        return response()->json([
            'users_role' => $role,
        ], 200);
    }
}
