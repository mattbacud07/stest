<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleUser;
use App\Models\Roles as Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\map;

class Roles extends Controller
{
    /** Asign Role to a specific User */
    public function assign_role(Request $request)
    {
        $user = $request->user;
        // $role_name = $request->role_name;
        $role_id = $request->role_id;
        $ssu = $request->ssu;

        try {
            $success = [];
            $usersExist = [];
            DB::beginTransaction();
            foreach ($user as $data) {
                $checkUserRole = RoleUser::where(['user_id' => $data['id'], 'role_id' => $role_id])->exists();
                if (!$checkUserRole) {
                    $create = RoleUser::create([
                        'user_id' => $data['id'],
                        'role_id' => $role_id,
                        'SSU' => $ssu
                    ]);
                    if(!$create){
                        throw new Exception('Error in creeating ROles');
                    }
                    $success[] = $data['user_name'];
                } else {
                    $usersExist[] = $data['user_name'];
                }
            }
            DB::commit();
            return response()->json([
                'userExist' => $usersExist,
                'succeed' => $success,
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }


    /** Delete Roles */
    public function delete_role(Request $request)
    {
        $id = $request->id;
        try {
            $delete = RoleUser::findOrFail($id);
            $delete->delete(); 

            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }


    /** Get the assigned Roles to all users */
    public function get_assigned_roles(Request $request)
    { 
    $role_id = $request->role;
        $user_role = RoleUser::with(['users' => function($q){
            $q->select('users.*', 'd.name', 'p.position_name')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.departments as d', 'users.department', '=', 'd.id')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.positions as p', 'users.position', '=', 'p.id');
        }])
        ->with('roles');

        if($request->has('role') && !empty($role_id)){
            $user_role->where('role_id', $role_id);
        }

        $role = $user_role->get();

        return response()->json([
            'users_role' => $role,
        ], 200);
    }

    /** Get all approver role users */
    public function get_approver_roles()
    {
        $getApproverRole = Role::where('role_name', 'Approver')->value('id');
        $approver_role = RoleUser::where('role_id', $getApproverRole)
            ->with(['users' => function($q){
                $q->select('users.*', 'd.name', 'p.position_name')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.departments as d', 'users.department', '=', 'd.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.positions as p', 'users.position', '=', 'p.id');
            }])
            ->with('roles')
            ->get();

        return response()->json([
            'approver_role' => $approver_role,
        ], 200);
    }





    /** ********************************************************** Roles && Permissions ******************************/
    public function get_role_name(){
        $data = Role::get();

        if($data){
            return response()->json(['role_name' => $data]);
        }
        return response()->json(['error' => "Something went wrong in fetching records"]);
    }

    //** Add Role Name [Permission, Module] */
    public function add_role_name(Request $request){
        $role_name = $request->role_name;
        $roleID = $request->roleID;
        $role_id_exist = null;
        $description = $request->description;
        $permissions = collect(Permission::defaultAccessType);
        $module = collect(Module::defaultModule);

        $check_roleID = Role::where('roleID', $roleID)->orWhere('role_name', $role_name)->first();
        if($check_roleID){
            $role_id_exist = true;
            return response()->json([
                'role_id_exist' => $role_id_exist,
            ]);
        }

        try {
            DB::beginTransaction();

            $addedRole = Role::create([
                'role_name' => $role_name,
                'roleID' => $roleID,
                'description' => $description,
            ])->roleID;
            if(!$addedRole){
                throw new Exception('Something went wrong in adding role');
            }

            //Permission Collection
            $permissions->each(function($accessType) use ($addedRole){
                Permission::create([
                    'role_id' => $addedRole,
                    'access_type' => $accessType,
                ]);
            });
            
            // Module Collection
            $module->each(function($module) use ($addedRole){
                Module::create([
                    'role_id' => $addedRole,
                    'title' => $module['title'],
                    'access_module' => $module['access_module'],
                    'module_type' => $module['module_type'],
                ]);
            });

            DB::commit();
            return response()->json([
                'success'=> true,
                // 'role_id_exist' => $role_id_exist,
            ],
                 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function delete_role_name(Request $request){
        $id = $request->id;

        try {
            $roleName = Role::findOrFail($id);
            $roleName->delete();
    
            return response()->json(['success' => true], 200); // Success
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Role not found'], 404); // Role not found
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500); // Server error
        }
    }

    /** Permissions */
    // get permissions
    public function get_permissions(){
        try {
            $getPermission = Permission::get();
            return response()->json(['permission' => $getPermission]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    //set permissions
    public function set_permissions(Request $request){
        $id = $request->id;
        $status = $request->status;
        try {
            $setPermission = Permission::findOrFail($id);
            $setPermission->update([
                'status' => $status
            ]);
            return response()->json(['success' => true]);
        } 
    catch (ModelNotFoundException $e) {
        // Handle the case where the model is not found
        return response()->json(['error' => 'Permission not found'. $e->getMessage()], 404);
    }
        catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    
    
    
    
    /** Modules */
    // get permissions
    public function get_modules(){
        try {
            $getModules = Module::get();
            return response()->json(['modules' => $getModules]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    //set permissions
    public function set_modules(Request $request){
        $id = $request->id;
        $status = $request->status;
        try {
            DB::beginTransaction();
            $setModules = Module::findOrFail($id);
            $setModules->update([
                'status' => $status
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } 
    catch (ModelNotFoundException $e) {
        // Handle the case where the model is not found
        DB::rollBack();
        return response()->json(['error' => 'Module not found'. $e->getMessage()], 404);
    }
        catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
