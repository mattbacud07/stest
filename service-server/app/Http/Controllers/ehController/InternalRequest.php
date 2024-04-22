<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\InternalRequest as WOInternalRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalRequest extends Controller
{
    public function get_engineers_data(){
        $role_name = 'Engineers';
        $engineers = DB::table('roles')->select('roles.user_id','u.first_name', 'u.last_name')
                    ->where('role_name', $role_name)
                    ->rightJoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'roles.user_id', '=', 'u.id')
                    ->get();

        return response()->json([
            'engineers' => $engineers,
        ], 200);
    }


    public function add_internal_process(Request $request){
        /** Check if service_id already exist in Internal Request table */
        $check_serviceId = WOInternalRequest::where('service_id', $request->service_id)->first();
        if($check_serviceId){
            return response()->json([
                'exist_service_id' => true,
            ]);
        }
        $data = [
            'service_id' => $request->service_id,
            'received_by' => $request->received_by,
            'type_of_activity' => $request->type_of_activity,
            'other' => $request->other,
            'delegation_date' => Carbon::now(),
            'delegated_to' => $request->delegated_to,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        try {
            DB::beginTransaction();
            $add_internal_request = WOInternalRequest::create($data);
            if(!$add_internal_request){
                throw new Exception('Error adding Internal Request'); 
            }
            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
