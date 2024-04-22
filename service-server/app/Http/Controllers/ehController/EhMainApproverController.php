<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EhMainApproverController extends Controller
{
    use GlobalVariables;

    public function index(Request $request)
    {
        $currentUserId = $request->user_id ?? 0;
        $getApprovalLevel = DB::table('approval_configuration')->where('user_id', $currentUserId)->first();
        $approval_level = $getApprovalLevel->approval_level ?? 0;
        
        $getListWorOrder = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approver_level', 'a.approver_name')
        ->where(['equipment_handling.status' => $approval_level, 'main_status' => self::ONGOING])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
        ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
        ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
        ->get();
        
        
        $service_id = $request->service_id ?? 0;
        $service_data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approver_level', 'a.approver_name')
        ->where(['equipment_handling.id' => $service_id])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
        ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
        ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
        ->get();

        // $service_datas = EhServicesModel::select('*')->where('id',$service_id)->get();

        return response()->json([
            'workOrder' => $getListWorOrder,
            'serviceData' => $service_data,
        ], 200);
    }


    /** 
     * Get Equipment Data
     */
    public function get_equipments(Request $request){
        $service_id = $request->service_id ?? 0;
        $equipments = DB::table('equipment_peripherals')->select('*','equipment_peripherals.id as equipment_id','m.item_code as itemCode','m.description as itemDescription')->where('service_id', $service_id)
                    ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.master_data as m', 'equipment_peripherals.item_id','=','m.id')
                    ->get();
        
        return response()->json([
            'equipments' => $equipments
        ]);
    }


    /**
     * Approve Work Order Request
     */
    public function approve_request(Request $request, Guard $guard){
        $user_id = $guard->user()->id;

        /** IT Department Approver */
        if($request->approval_level === self::IT_DEPARTMENT){
            $items = $request->item;
            try {
                DB::beginTransaction();
                foreach ($items as $item) {
                    $update_item_peripheral = DB::table('equipment_peripherals')
                        ->whereIn('id', [$item['id']]) // Wrap $item['id'] in an array
                        ->update(['serial_number' => $item['serial']]);
                    
                    if (!$update_item_peripheral) {
                        throw new Exception('Failed to update equipment peripherals');
                    }
                }
                $updateStatus = DB::table('equipment_handling')
                        ->where('id', $request->service_id)
                        ->update([
                            'status' => self::APM_NSM_SM,
                            'updated_at' => Carbon::now(),
                        ]);
                if(!$updateStatus){
                    throw new Exception('Failed to update approver status');
                }
                $logApproval = DB::table('approvals')->insert([
                    'service_id' => $request->service_id,
                    'user_id' => $user_id,
                    'remarks' => $request->remark,
                    'level' => 1,
                    'status' => self::ONGOING,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                if(!$logApproval){
                    throw new Exception('Failed to log approval details');
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
        }else{
            if($request->approval_level === self::APM_NSM_SM){
                $approval_level =  self::WIM; 
                $level = self::APM_NSM_SM;
            }elseif($request->approval_level === self::WIM){
                $approval_level =  self::SERVICE_TL;
                $level = self::WIM;
            }elseif($request->approval_level === self::SERVICE_TL){
                $approval_level =  self::SERVICE_HEAD_ENGINEER;
                $level = self::SERVICE_TL;
            }elseif($request->approval_level === self::SERVICE_HEAD_ENGINEER){
                $approval_level =  self::BILLING_WIM;
                $level = self::SERVICE_HEAD_ENGINEER;
            }elseif($request->approval_level === self::BILLING_WIM){
                $approval_level =  self::PARTIAL_COMPLETE;
                $level = self::BILLING_WIM;
            }else{
                throw new Exception('Invalid approver');
            }
            
            /**
             * Update Status - General Query
             */
            try {
                DB::beginTransaction();

                $updateStatus = DB::table('equipment_handling')
                            ->where('id', $request->service_id)
                            ->update([
                                'status' => $approval_level,
                                'updated_at' => Carbon::now(),
                            ]);
                if(!$updateStatus){
                    throw new Exception('Failed to update approver status');
                }
                $logApproval = DB::table('approvals')->insert([
                    'service_id' => $request->service_id,
                    'user_id' => $user_id,
                    'remarks' => $request->remark,
                    'level' => $level,
                    'status' => self::ONGOING,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                if(!$logApproval){
                    throw new Exception('Failed to log approval details');
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


    /**
     * DisApprove Work Order Request
     */
    public function disapprove_request(Request $request){
        $user_id = $request->id;
        $service_id = $request->service_id;
        $remark = $request->remark;
        $approval_level = $request->approval_level;
        $approval_track = $approval_level + 1;


        try {
            DB::beginTransaction();

            $logApproval = DB::table('approvals')->insert([
                'service_id' => $service_id,
                'user_id' => $user_id,
                'remarks' => $remark,
                'level' => $approval_level,
                'status' => self::DISAPPROVED,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            if(!$logApproval){
                throw new Exception('Failed to log approval details');
            }

            $update_main_status = DB::table('equipment_handling')
                                ->where('id', $service_id)
                                ->update([
                                    'status' => $approval_track,
                                    'main_status' => self::DISAPPROVED,
                                    'updated_at' => Carbon::now(),
                                ]);
            if(!$update_main_status){
                throw new Exception('Error. Something went wrong.');
                // throw new QueryException([],[],[],new \Exception());
            }

            // We will use it on Resubmit - Saka pa lang mag update after resubmit
            // $updateStatus = DB::table('approvals')
            //                 ->where('id', $service_id)
            //                 ->update([
            //                     'status' => self::DISAPPROVED,
            //                     'updated_at' => Carbon::now(),
            //                 ]);
            // if(!$updateStatus){
            //     throw new Exception('Failed to update approvals table - field status');
            // }

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
