<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\InternalExternalRequest as IEModel;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Models\WorkOrder\InternalRequest;
use App\Services\ApprovalService;
use App\Services\PM\GeneratePMSched;
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

    protected $approvalService;
    protected $generatePMSched;
    public function __construct(ApprovalService $approvalService, GeneratePMSched $generatePMSched)
    {
        $this->approvalService = $approvalService;
        $this->generatePMSched = $generatePMSched;
    }

    // public function index(Request $request)
    // {
    //     $currentUserId = $request->user_id ?? 0;
    //     $getApprovalLevel = DB::table('approval_configuration')->where('user_id', $currentUserId)->first();
    //     $approval_level = $getApprovalLevel->approval_level ?? 0;

    //     $getListWorOrder = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'iE.name as request_name', 'a.approver_level', 'a.approver_name')
    //         ->where(['equipment_handling.status' => $approval_level, 'main_status' => self::ONGOING])
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
    //         ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
    //         ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
    //         ->get();


    //     $service_id = $request->service_id ?? 0;
    //     $service_data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'i.area', 'u.first_name', 'u.last_name', 'ui.first_name as fname', 'ui.last_name as lname', 'ua.first_name as assigned_tl_fname', 'ua.last_name as assigned_tl_lname', 'iE.name as request_name', 'a.approver_level', 'a.approver_name')
    //         ->where(['equipment_handling.id' => $service_id])
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ui', 'equipment_handling.installer', '=', 'ui.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ua', 'equipment_handling.tl_assigned', '=', 'ua.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
    //         ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
    //         ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
    //         ->get();

    //     $service_datas = EhServicesModel::select('*')->where('id',$service_id)->get();

    //     return response()->json([
    //         'workOrder' => $getListWorOrder,
    //         'serviceData' => $service_data,
    //     ], 200);
    // }


    /** 
     * Get Equipment Data
     */
    public function get_equipments(Request $request)
    {
        $service_id = $request->service_id ?? 0;
        $equipments = DB::table('equipment_peripherals')->select('*', 'equipment_peripherals.id as equipment_id', 'm.item_code as itemCode', 'm.description as itemDescription')->where('service_id', $service_id)
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'equipment_peripherals.item_id', '=', 'm.id')
            ->get();

        return response()->json([
            'equipments' => $equipments
        ]);
    }


    /**
     * Approve Work Order Request
     */
    public function approve_request(Request $request, Guard $guard)
    {
        $user_id = $guard->user()->id;
        $service_id = $request->service_id;
        $approval_level = $request->approval_level;
        $remark = $request->remark ?? '';
        $institution_area = $request->institution_area ?? null;
        $status = $request->status ?? 0;

        $dataArray = [];


        /** Approve Request */
        try {
            DB::beginTransaction();
            if ($approval_level === self::IT_DEPARTMENT) {
                $this->approvalService->updateEquipmentPeripherals($request->item);
                $this->approvalService->updateStatus($service_id, self::APM_NSM_SM);
                $this->approvalService->logApproval($service_id, $user_id, $remark, self::IT_DEPARTMENT, self::ONGOING);
            } else {
                if ($status === self::INSTALLATION_TL) {
                    $next_approver =  self::INSTALLATION_ENGINEER;
                    $approval_level = self::INSTALLATION_TL;
                    $main_status = self::INSTALLING;

                    $tl_assigned = $request->tl_assigned ?? null;
                    $installer = $request->installer ?? null;
                    $assigned_date = Carbon::now();

                    $dataArray = [
                        'tl_assigned' => $tl_assigned,
                        'assigned_date' => $assigned_date,
                        'installer' => $installer
                    ];
                }
                elseif ($status === self::INSTALLATION_ENGINEER) {
                    $next_approver =  self::INSTALLATION_ENGINEER + 1;
                    $approval_level = self::INSTALLATION_ENGINEER + 1;
                    $main_status = self::COMPLETE;

                    $date_installed = Carbon::now();

                    /** Get all the Equipments for automation of PM Sched */
                    $equipments = EquipmentPeripherals::select('equipment_peripherals.*', 'p.equipment','p.schedule')
                        ->leftJoin('pm_setting as p', 'equipment_peripherals.item_id', '=', 'p.equipment')
                        // ->leftJoin('equipment_handling as eh', 'equipment_peripherals.service_id', '=', 'eh.id')
                        ->where('equipment_peripherals.service_id', $service_id)
                        ->where('equipment_peripherals.category', 'Equipment')
                        ->groupBy('equipment_peripherals.item_id')
                        ->get();

                    /** Get External Request Option */
                    $getExternalRequestOption = EhServicesModel::select('request_type')->where('id', $service_id)->first();
                    $externalRequestOptionArray = [IEModel::ReagentTieup, IEModel::Purchased]; //remove 5, accrding to SDpt

                    if($getExternalRequestOption && in_array($getExternalRequestOption->request_type, $externalRequestOptionArray)){
                        //  return $this->generatePMSched->calculateSchedFrequency($date_installed, 'Quarterly', Carbon::now()->endOfYear());
                        $this->generatePMSched->calculateNextPMSchedule($date_installed, $equipments);
                    }
                    
                    /** Update for Equipment Installation */
                    $dataArray = [
                        'date_installed' => $date_installed
                    ];

                    

                } else {
                    //Extract data from $nextApproval [nextApproval, main_status, area]
                    $nextApproval = $this->approvalService->getNextApprovalLevel($approval_level, $institution_area);
                    [$next_approver, $main_status, $area] = array_pad($nextApproval, 3, null);

                    if ($area !== self::LUZON && $area !== null) {
                        $next_approver = 28;
                    }
                }

                $this->approvalService->updateStatusGeneral(
                    $service_id,
                    $dataArray,
                    $next_approver,
                    $main_status
                );
                $this->approvalService->logApproval($service_id, $user_id, $remark, $approval_level, $main_status);
            }
            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * DisApprove Work Order Request
     */
    public function disapprove_request(Request $request)
    {
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
            if (!$logApproval) {
                throw new Exception('Failed to log approval details');
            }

            $update_main_status = DB::table('equipment_handling')
                ->where('id', $service_id)
                ->update([
                    'status' => $approval_track,
                    'main_status' => self::DISAPPROVED,
                    'updated_at' => Carbon::now(),
                ]);
            if (!$update_main_status) {
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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
