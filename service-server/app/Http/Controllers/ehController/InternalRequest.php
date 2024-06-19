<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\WorkOrder\InternalRequest as WOInternalRequest;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalRequest extends Controller
{
    use GlobalVariables;

    /**
     * Get Engineers Data
     */
    public function get_engineers_data(){
        $role_name = self::engineerRole;
        $engineers = DB::table('roles')->select('roles.user_id','u.first_name', 'u.last_name')
                    ->where('role_name', $role_name)
                    ->rightJoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'roles.user_id', '=', 'u.id')
                    ->get();

        return response()->json([
            'engineers' => $engineers,
        ], 200);
    }

    /**
     * Get Internal Request Details 
     */
    public function get_internal_request_details(Request $request){
        $requested_id = $request->requested_id;

        try {
            $getDetails = WOInternalRequest::where('id',$requested_id)->get();

            if(!$getDetails){
                throw new Exception('Something went wrong in fetching data');
            }
            return response()->json([
                'details' => $getDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Submit Internal Servicing Request
     */
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

    /**
     * Get All Internal Servicing Request for Specific User
     */
    public function getInternalRequest(Request $request){
        $user_id = $request->user_id;
        try {
            $get_request = WOInternalRequest::where('delegated_to', $user_id)
            // ->join(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'i.id','=','equipment_handling.institution')
            // ->join('equiment_handling as e','')
            ->with(['equipment_handling' => function($query){
                $query->select(
                    'equipment_handling.id',
                    'institution',
                    'proposed_delivery_date',
                    'equipment_handling.created_at',
                    'i.name as institution_name'
                )
                ->join(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'i.id','=','equipment_handling.institution');
            }])
            ->with('internal_external_name')
            ->get();
            return response()->json([
               'request' => $get_request, 
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }


    /** Accept Internal Request */
    public function accept_internal_request(Request $request){
        $requested_id = $request->requested_id;
        $service_id = $request->service_id;
        try {
            DB::beginTransaction();
            $accept_request = WOInternalRequest::where('id',$requested_id)->update([
                'date_started' => Carbon::now(),
                'status' => self::I_ONGOING,
            ]);
            if(!$accept_request){
                throw new Exception('Error updating. Please refresh. '. $requested_id);
            }
            DB::commit();
            return response()->json([
               'success' => true, 
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }



    /** Internal Servicing Request Completed */
    public function internal_servicing_completed(Request $request){
        $request_id = $request->requested_id;
        $service_id = $request->service_id;

        try {
            DB::beginTransaction();
            WOInternalRequest::where('id',$request_id)->update([
                'status' => self::I_COMPLETE,
                'accomplished_date' => Carbon::now(),
            ]);
            EhServicesModel::where('id', $service_id)->update([
                'status' => self::SERVICE_HEAD_ENGINEER,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }

    }


}
