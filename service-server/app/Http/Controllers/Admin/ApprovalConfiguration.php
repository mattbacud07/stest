<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalConfigModel;
use App\Models\Approvals;
use App\Models\authLogin\UserModel;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class ApprovalConfiguration extends Controller
{
    public function index() //Approvers and Users
    {
        // $users = DB::connection('mysqlSecond')->select('SELECT * FROM users');

        // $approvers = DB::select('
        //     SELECT a.*, u.first_name, u.last_name, u.email, p.position_name, d.name
        //     FROM approval_configuration a
        //     JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.users u ON a.user_id = u.id
        //     JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.positions p ON u.position = p.id
        //     JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.departments d ON u.department = d.id
        //     ORDER BY a.id DESC
        // ');
        
        $users = UserModel::select(
            'users.id',
            'users.first_name',
            'users.last_name',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name"),
            'users.email',
            'p.position_name',
            'd.name'
        )
            ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.positions as p', 'users.position', '=', 'p.id')
            ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.departments as d', 'users.department', '=', 'd.id')
            ->get();

        return response()->json([
            // 'approvers' => $approvers,
            'users' => $users,
        ]);
    }


    /**
     * Approver Data
     */
    // public function get_approvers_data()
    // {
    //     $users = DB::select('
    //         SELECT a.*,u.first_name , u.last_name, concat(u.first_name , u.last_name) as user_name, p.position_name, d.name
    //         FROM approval_configuration a
    //         JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.users u ON a.user_id = u.id
    //         JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.positions p ON u.position = p.id
    //         JOIN ' . DB::connection('mysqlSecond')->getDatabaseName() . '.departments d ON u.department = d.id
    //     ');

    //     return response()->json([
    //         'users' => $users,
    //     ]);
    // }



    /**
     * Assigning Approver.
     */
    public function update_approver_config(Request $request)
    {

        $datas = $request->update_approval ?? [];

        try {
            DB::beginTransaction();
            foreach ($datas as $data) {
                $update_role_user = [
                    'sbu' => $data['sbu'],
                    'satellite' => $data['satellite'],
                ];
                $roleUser  = RoleUser::find($data['role_user_id']);
                $roleUser->update($update_role_user);


                $categories = [
                    ['key' => 'eh_level', 'category' => 1],
                    ['key' => 'pullout_level', 'category' => 2],
                ];

                foreach ($categories as $category) {
                    $get_levels = ApprovalConfigModel::where([
                        'role_user_id' => $data['role_user_id'],
                        'user_id' => $data['user_id'],
                        'approver_category' => $category['category']
                    ])->pluck('approval_level')->toArray();

                    //levels in array
                    $level_array = array_map(function ($data) {
                        return $data['level'];
                    }, $data[$category['key']]);
                    //level_name in array
                    $level_name_array = array_column($data[$category['key']], 'level_name', 'level');

                    $levels_to_delete = array_diff($get_levels, $level_array);
                    $levels_to_insert = array_diff($level_array, $get_levels);

                    /** Delete record - unchecked */
                    if (!empty($levels_to_delete)) {
                        $delete_level = ApprovalConfigModel::where([
                            'role_user_id' => $data['role_user_id'],
                            'user_id' => $data['user_id'],
                            'approver_category' => $category['category']
                        ])
                            ->whereIn('approval_level', $levels_to_delete)
                            ->delete();
                        if (!$delete_level) {
                            throw new \Exception('Unable to delete eh level');
                        }
                    }


                    /** Insert checked or newly selected level */
                    if (!empty($levels_to_insert)) {
                        $insert_data = [];
                        foreach ($levels_to_insert as $level) {
                            $insert_data[] = [
                                'role_user_id' => $data['role_user_id'],
                                'user_id' => $data['user_id'],
                                'roleID' => $data['roleID'],
                                'approval_level' => $level,
                                'approval_level_name' => $level_name_array[$level] ?? '',
                                'approver_category' => $category['category'],
                            ];
                        }
                        ApprovalConfigModel::insert($insert_data);
                    }
                }
            }
            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /** Assing Supervisor */
    public function assign_supervisor(Request $request)
    {
        $supervisors = $request->supervisor;

        try {
            DB::beginTransaction();
            foreach ($supervisors as $supervisor) {
                $q = RoleUser::find($supervisor['id']);
                $update = $q->update([
                    'supervisor_id' => $supervisor['supervisor_id']
                ]);
                if (!$update) {
                    throw new \Exception('Unable to assign supervisor');
                }
            }
            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /** Assing SBU */
    public function assign_sbu(Request $request)
    {
        $sbus = $request->sbu;

        try {
            DB::beginTransaction();
            foreach ($sbus as $sbu) {
                $q = RoleUser::find($sbu['id']);
                $update = $q->update([
                    'sbu' => $sbu['sbu']
                ]);
                if (!$update) {
                    throw new \Exception('Unable to assign sbu');
                }
            }
            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Delete Approver.
     */
    public function delete_approver(Request $request)
    {
        $ids = $request->ids;
        try {
            $delete_approver = DB::table('approval_configuration')->whereIn('id', $ids)->delete();
            // dd($id);
            if ($delete_approver) {
                return response()->json(['isDeleted' => 'Successfully deleted'], 200);
            } else {
                return response()->json(['error' => 'Something went wrong'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong to our database'], 500);
            Log::Error('Deleting Approver Error - ' . $th->getMessage());
        }

        return redirect()->back();
    }






    /**
     * Get Approval History
     */
    // public function approval_history(Request $request)
    // {
    //     try {
    //         $approvalHistory = Approvals::where('service_id', $request->service_id)
    //         ->with('users')
    //         ->get();
    //         return response()->json([
    //             'approval_history' => $approvalHistory,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }
}
