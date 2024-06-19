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
        $service_data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'i.area', 'u.first_name', 'u.last_name', 'ui.first_name as fname', 'ui.last_name as lname', 'ua.first_name as assigned_tl_fname', 'ua.last_name as assigned_tl_lname', 'e.name as request_name','iR.name as internal_name','a.approver_level', 'a.approver_name')
        ->where(['equipment_handling.id' => $service_id])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as ui', 'equipment_handling.installer', '=', 'ui.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as ua', 'equipment_handling.tl_assigned', '=', 'ua.id')
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
        $service_id = $request->service_id;
        $approval_level = $request->approval_level;
        $institution_area = $request->institution_area;
        $tl_assigned = null;
        $assigned_date = null;
        $installer = null;
        $date_installed = null;
        $status = $request->status ?? 0;

        /** IT Department Approver */
        if($approval_level === self::IT_DEPARTMENT){
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
                    'level' => self::IT_DEPARTMENT,
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
            $main_status = self::ONGOING;
            if($approval_level === self::APM_NSM_SM){
                $approval_level =  self::WIM; 
                $level = self::APM_NSM_SM;
            }elseif($approval_level === self::WIM){
                $approval_level =  self::SERVICE_TL;
                $level = self::WIM;
            }elseif($approval_level === self::SERVICE_TL){
                $approval_level =  self::SERVICE_HEAD_ENGINEER;
                $level = self::SERVICE_TL;
            }elseif($approval_level === self::SERVICE_HEAD_ENGINEER){
                $approval_level =  self::BILLING_WIM;
                $level = self::SERVICE_HEAD_ENGINEER;
            }elseif($approval_level === self::BILLING_WIM){
                $approval_level =  self::INSTALLATION_TL;
                $level = self::BILLING_WIM;
                $main_status = self::INSTALLING;

                //Check Institution Area if not Luzon
                // if($institution_area === self::LUZON){

                // }
            }elseif($status === self::INSTALLATION_TL){
                $approval_level =  self::INSTALLATION_ENGINEER;
                $level = self::INSTALLATION_TL;
                $main_status = self::INSTALLING;

                $tl_assigned = $request->tl_assigned ?? null;
                $installer = $request->installer ?? null;
                $assigned_date = Carbon::now();
                // $date_installed = $request->date_installed ?? null;
            }elseif($status === self::INSTALLATION_ENGINEER){
                $approval_level =  self::INSTALLATION_ENGINEER + 1;
                $level = self::INSTALLATION_ENGINEER + 1;
                $main_status = self::COMPLETE;

                $get_equipments  = DB::table('equipment_peripherals')->select('equipment_peripherals.*','m.category')
                    ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.master_data as m', 'equipment_peripherals.item_id', '=', 'm.id')
                    ->leftJoin('equipment_handling as e', 'equipment_peripherals.service_id', '=', 'e.id')
                    ->where([
                        'equipment_peripherals.service_id' => $service_id,
                        'equipment_peripherals.category' => 'Equipment',
                        ])
                    ->get();
                    
                    /** Insert Equipment for Auto PM Schedule */
                    DB::beginTransaction();
                    try {
                        foreach($get_equipments as $equipment){
                            $saveEquipment = DB::table('preventive_maintenance')->insert([
                                'equipment' => $equipment['item_id'],
                                'institution' => $equipment['institution'],
                                'serial_number' => $equipment['serial_number'],
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                        if(!$saveEquipment){
                            throw new Exception('Inserting failed');
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    
                    
            }
            else{
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
                                'tl_assigned' => $tl_assigned,
                                'assigned_date' => $assigned_date,
                                'installer' => $installer,
                                'date_installed' => $date_installed,
                                'status' => $approval_level,
                                'main_status' => $main_status,
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
                    'status' => $main_status,
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
