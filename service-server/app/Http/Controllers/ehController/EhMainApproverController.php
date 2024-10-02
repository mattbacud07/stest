<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Mail\EHNotification;
use App\Models\EhServicesModel as EH;
use App\Models\InternalExternalRequest as IEModel;
use App\Models\MasterDataInstitution;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Models\WorkOrder\InternalRequest;
use App\Services\ActionsDoneService;
use App\Services\ApprovalService;
use App\Services\PM\GeneratePMSched;
use App\Services\SendNotification;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EhMainApproverController extends Controller
{
    use GlobalVariables;

    protected $approvalService;
    protected $generatePMSched;
    protected $action;
    protected $sendNotification;
    public function __construct(
        ApprovalService $approvalService,
        GeneratePMSched $generatePMSched,
        ActionsDoneService $actions,
        SendNotification $sendNotification
    ) {
        $this->approvalService = $approvalService;
        $this->generatePMSched = $generatePMSched;
        $this->action = $actions;
        $this->sendNotification = $sendNotification;
    }



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
        // $user_id = $guard->user()->id;
        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        // $approval_level = $request->approval_level;
        // $approval_level = 0;
        $remark = $request->remark ?? '';
        $institution_area = $request->institution_area ?? null;
        // $status = $request->status ?? 0;
        $ssu = $request->ssu ?? '';

        $dataArray = [];

        /** Get the current approval level (status) for the request in the equipment handling table */
        $user_approver_level = DB::table('approval_configuration')
            ->where('user_id', $user_id)
            ->value('approval_level');
        $institution_id = EH::where('id', $service_id)->value('institution');
        // Check if the institution ID is valid
        if ($institution_id) {
            $getInstitutionArea = MasterDataInstitution::where('id', $institution_id)->value('area') ?? null;
        }

        $status = EH::where('id', $service_id)->value('status');
        // Add checks to ensure the values were found
        if (is_null($user_approver_level) || is_null($status)) {
            return response()->json(['error' => 'Approver level or status not found'], 404);
        }



        /** Approve Request */
        try {
            DB::beginTransaction();

            if ($status === EH::IT_DEPARTMENT) {
                $this->approvalService->updateEquipmentPeripherals($request->item);
                $this->approvalService->updateStatus($service_id, EH::APM_NSM_SM);
                $this->approvalService->logApproval($service_id, $user_id, $remark, EH::IT_DEPARTMENT, EH::ONGOING);
            } else {
                if ($status === EH::SERVICE_HEAD_ENGINEER) {
                    /** Set SSU */
                    $dataArray = [
                        'ssu' => $ssu,
                    ];
                } elseif ($status === EH::OUTBOUND) {
                    $status = EH::OUTBOUND;

                    $final_installation_date = $request->final_installation_date ?? null;
                    $driver = $request->driver ?? null;

                    $dataArray = [
                        'final_installation_date' => $final_installation_date,
                        'driver' => $driver,
                    ];
                } elseif ($status === EH::INSTALLATION_TL) {
                    $status = EH::INSTALLATION_TL;

                    $engineer = $request->engineer ?? null;
                    $assigned_date = Carbon::now();

                    $dataArray = [
                        'tl_assigned' => $user_id,
                        'assigned_date' => $assigned_date,
                        'installer' => $engineer
                    ];
                } elseif ($status === EH::INSTALLATION_ENGINEER) {
                    $status = EH::INSTALLATION_ENGINEER;

                    $date_installed = Carbon::now();
                    $actions_done_data = [];
                    foreach ($request->actionsDone as $action) {
                        $actions_done_data[] = [
                            'serial' => $action['serial'],
                            'equipment_id' => $action['equipment_id'],
                            'service_id' => $action['service_id'],
                            'action' => $action['action'],
                            'work_type' => $action['work_type'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    /** Submit Actions Done */
                    $this->action->declare_actions_done($actions_done_data);



                    /** Get all the Equipments for automation of PM Sched */
                    $equipments = EquipmentPeripherals::select('equipment_peripherals.*', 'p.equipment', 'p.schedule','eh.institution','eh.ssu')
                        ->leftJoin('pm_setting as p', 'equipment_peripherals.item_id', '=', 'p.equipment')
                        ->leftJoin('equipment_handling as eh', 'equipment_peripherals.service_id', '=', 'eh.id')
                        ->where('equipment_peripherals.service_id', $service_id)
                        ->where('equipment_peripherals.category', 'Equipment')
                        // ->groupBy('equipment_peripherals.item_id')
                        ->get();



                    /** Get External Request Option */
                    $getExternalRequestOption = EH::select('request_type')->where('id', $service_id)->first();
                    $externalRequestOptionArray = [IEModel::ReagentTieup, IEModel::Purchased]; //remove 5, accrding to SDpt

                    if ($getExternalRequestOption && in_array($getExternalRequestOption->request_type, $externalRequestOptionArray)) {
                        $this->generatePMSched->calculateNextPMSchedule($date_installed, $equipments);
                    }

                    /** Update for Equipment Installation */
                    $dataArray = [
                        'date_installed' => $date_installed,
                        // 'equipments' => $equipments,
                    ];
                }
                // else {


                // }

                //Extract data from $nextApproval [nextApproval, main_status, area]
                $nextApproval = $this->approvalService->getNextApprovalLevel($status, $getInstitutionArea);
                [$next_approver, $main_status, $area] = array_pad($nextApproval, 3, null);

                $this->approvalService->updateStatusGeneral(
                    $service_id,
                    $dataArray,
                    $next_approver,
                    $main_status
                );
                $this->approvalService->logApproval($service_id, $user_id, $remark, $user_approver_level, $main_status);

                $email = 'joshenpolen@gmail.com';
                $name = $guard->user()->first_name . ' ' . $guard->user()->last_name;
                Mail::to($email)->queue(new EHNotification($name));
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'area' => $getInstitutionArea,
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
    public function disapprove_request(Request $request, Guard $guard)
    {
        // $user_id = $guard->user()->id;
        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        $remark = $request->remark;
        // $approval_level = $request->approval_level;/** Get the current approval level (status) for the request in the equipment handling table */
        $approval_level_of_request = EH::where('id', $service_id)->value('status');
        // $approval_track = $approval_level_of_request + 1;


        try {
            DB::beginTransaction();

            $logApproval = DB::table('approvals')->insert([
                'service_id' => $service_id,
                'user_id' => $user_id,
                'remarks' => $remark,
                'level' => $approval_level_of_request,
                'status' => EH::DISAPPROVED,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            if (!$logApproval) {
                throw new Exception('Failed to log approval details');
            }

            $update_main_status = DB::table('equipment_handling')
                ->where('id', $service_id)
                ->update([
                    // 'status' => $approval_track,
                    'main_status' => EH::DISAPPROVED,
                    'updated_at' => Carbon::now(),
                ]);
            if (!$update_main_status) {
                throw new Exception('Error. Something went wrong.');
            }

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
