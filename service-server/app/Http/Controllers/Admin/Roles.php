<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\map;

class Roles extends Controller
{
    /** Asign Role to a specific User */
    public function assign_role(Request $request){
        $user = $request->user;

        try {
            $success = [];
            $usersExist = [];
            foreach($user as $data){
                $checkId = DB::table('roles')->where('user_id', $data['id'])->select();
                if(!$checkId->exists()){
                    DB::table('roles')->insert([
                        'user_id' => $data['id'],
                        'role_name' => $request->role_name,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $success[] = $data['user_name'];
                }else{
                    $usersExist[] = $data['user_name'];
                }
                
            }
            return response()->json([
                'userExist' => $usersExist,
                'succeed' => $success,
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
        
    }


    /** Get the assigned Roles to all users */
    public function get_assigned_roles(){
        $user_role = DB::table('roles')->select('roles.*', 'u.first_name', 'u.last_name','d.name','p.position_name')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'roles.user_id', '=', 'u.id')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.departments as d', 'u.department', '=', 'd.id')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.positions as p', 'u.position', '=', 'p.id')
            ->get();

        return response()->json([
            'users_role' => $user_role,
        ], 200);
    }
}
