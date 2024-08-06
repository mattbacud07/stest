<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalConfiguration extends Controller
{
    public function index() //Approvers and Users
    {
        // $users = DB::connection('mysqlSecond')->select('SELECT * FROM users');

        $approvers = DB::select('
            SELECT a.*, u.first_name, u.last_name, u.email, p.position_name, d.name
            FROM approval_configuration a
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.users u ON a.user_id = u.id
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.positions p ON u.position = p.id
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.departments d ON u.department = d.id
            ORDER BY a.id DESC
        ');

        $users = DB::select('
            SELECT u.id, u.first_name, u.last_name, concat(u.first_name , u.last_name) as user_name, u.email, p.position_name, d.name
            FROM '.DB::connection('mysqlSecond')->getDatabaseName().'.users u
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.positions p ON u.position = p.id
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.departments d ON u.department = d.id
        ');

        return response()->json([
            'approvers' => $approvers,
            'users' => $users,
        ]);
    }


    /**
     * Approver Data
     */
    public function get_approvers_data()
    {
        $users = DB::select('
            SELECT a.*,u.first_name , u.last_name, concat(u.first_name , u.last_name) as user_name, p.position_name, d.name
            FROM approval_configuration a
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.users u ON a.user_id = u.id
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.positions p ON u.position = p.id
            JOIN '.DB::connection('mysqlSecond')->getDatabaseName().'.departments d ON u.department = d.id
        ');

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Assigning Approver.
     */
    public function assign_approver(Request $request)
    {
        $data = [
            'user_id' => $request->input('user_id'),
            'approval_level' => $request->input('approver_level'),
            'approval_level_name' => $request->input('approver_level_name'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $check_approver_exist = DB::table('approval_configuration')->where(['user_id' => $request->input('user_id')])->select('*')->get(); //, 'approval_level' => $request->input('approver_level')

        if (!$check_approver_exist->isEmpty()) {
            return response()->json(['error' => 'Already exist as an approver'], 200);
        } else {
            $assign_approver = DB::table('approval_configuration')->insert($data);
            if ($assign_approver) {
                return response()->json(['success' => 'Successfully added as approver'], 200);
            } else {
                return response()->json(['error' => 'Something went wrong'], 200);
            }
        }
    }



    /**
     * Get Approval History
     */
    public function approval_history(Request $request){
        try {
            $approvalHistory = DB::table('approvals')->where('service_id', $request->service_id)->select('*')->get();
           return response()->json([
                'approval_history' => $approvalHistory,
           ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
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
            Log::Error('Deleting Approver Error - '.$th->getMessage());
        }

        return redirect()->back();
    }
}
