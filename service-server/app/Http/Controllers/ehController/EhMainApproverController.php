<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Mail\EHNotification;
use App\Models\Approvals;
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
        $category = $request->category ?? '';
        $equipments = EquipmentPeripherals::select(
                'equipment_peripherals.*',
                'equipment_peripherals.id as equipment_id',
                'm.item_code as itemCode',
                'm.description as itemDescription',
                'md.serial',
                'md.sbu'
            )
            ->where([
                'service_id' => $service_id,
                'request_type' => $category,
            ])
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'equipment_peripherals.item_id', '=', 'm.id')
            ->leftJoin('service_master_data as md', 'equipment_peripherals.service_master_data_id', '=', 'md.id')
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
        $this->middleware(['permission:Equipment Handling,approve']);

        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        $remark = $request->remark ?? '';
        $levelFromRequest = $request->level; // use to check wether current level = to submit request level
        $satellite = $request->satellite ?? '';
        $receiving_option = $request->receiving_option ?? null;

        $dataArray = [];

        $date_now = Carbon::now();


        /** Approve Request */
        try {
            DB::beginTransaction();

            /** Get the current approval level (level) for the request in the equipment handling table */
            $user_approver_level = DB::table('approval_configuration')
                ->where('user_id', $user_id)
                ->where('approver_category', EH::EH_INSTALLATION)
                ->value('approval_level');

            $getExternalRequestOption = EH::select('request_type')->where('id', $service_id)->first(); // Get External Option Request for Identifying Satellite Shipment
            $request_type = $getExternalRequestOption->request_type;

            $level = EH::where('id', $service_id)->value('level');
            // Add checks to ensure the values were found
            if (is_null($user_approver_level) || is_null($level)) {
                return response()->json(['error' => 'Approver level or status not found'], 404);
            }
            if($level != $levelFromRequest){
                return response()->json(['error' => 'Approver level does not match'], 404);
            }

            if ($level === EH::IT_DEPARTMENT) {
                $this->approvalService->updateEquipmentPeripherals($request->serial);
                // $this->approvalService->updateStatus($service_id, EH::SM_SER);
                $dataArray = [];
            } else {
                if ($level === EH::OUTBOUND) {
                    $level = EH::OUTBOUND;

                    $final_installation_date = $request->final_installation_date ?? null;
                    $driver = $request->driver ?? null;

                    $dataArray = [
                        'final_installation_date' => $final_installation_date,
                        'driver' => $driver,
                        'receiving_option' => $receiving_option,
                    ];
                } 
                // elseif ($level === EH::INSTALLATION_TL) {
                //     $level = EH::INSTALLATION_TL;

                //     $engineer = $request->engineer ?? null;

                //     $dataArray = [
                //         'tl_assigned' => $user_id,
                //         'assigned_date' => $date_now,
                //         'installer' => $engineer
                //     ];
                // } elseif ($level === EH::INSTALLATION_ENGINEER) {
                //     $level = EH::INSTALLATION_ENGINEER;

                //     // $date_installed = Carbon::now();
                //     $actions_done_data = [];
                //     foreach ($request->actionsDone as $action) {
                //         $actions_done_data[] = [
                //             'serial' => $action['serial'],
                //             'equipment_id' => $action['equipment_id'],
                //             'service_id' => $action['service_id'],
                //             'action' => $action['action'],
                //             'work_type' => $action['work_type']
                //         ];
                //     }

                //     /** Submit Actions Done */
                //     $this->action->declare_actions_done($actions_done_data);

                //     /** Get all the Equipments for automation of PM Sched */
                //     $equipments = EquipmentPeripherals::select('equipment_peripherals.*', 'p.equipment', 'p.schedule', 'eh.institution', 'eh.ssu')
                //         ->leftJoin('pm_setting as p', 'equipment_peripherals.item_id', '=', 'p.equipment')
                //         ->leftJoin('equipment_handling as eh', 'equipment_peripherals.service_id', '=', 'eh.id')
                //         ->where('equipment_peripherals.service_id', $service_id)
                //         ->where('equipment_peripherals.category', 'Equipment')
                //         ->where('equipment_peripherals.request_type', self::EH)
                //         ->get();

                //     /** Get External Request Option */
                //     $externalRequestOptionArray = [IEModel::ReagentTieup, IEModel::Purchased]; //remove 5, accrding to SDpt

                //     if ($getExternalRequestOption && in_array($request_type, $externalRequestOptionArray)) {
                //         $this->generatePMSched->calculateNextPMSchedule($date_now, $equipments);
                //     }

                //     /** Update for Equipment Installation */
                //     $dataArray = [
                //         'date_installed' => $date_now,
                //     ];
                // }

                // $email = 'joshenpolen@gmail.com';
                // $name = $guard->user()->first_name . ' ' . $guard->user()->last_name;
                // Mail::to($email)->queue(new EHNotification($name));
            }


            /** Processing */
            //Extract data from $nextApproval [nextApproval, main_status, area]
            $nextApproval = $this->approvalService->getNextApprovalLevel($level, $request_type, $receiving_option);
            [$next_approver, $main_status] = $nextApproval;

            $update_equipment_handling = $this->approvalService->updateStatusGeneral(
                $service_id,
                $dataArray,
                $next_approver,
                $main_status
            );

            $update_log_approval = $this->approvalService->updateLogApproval(
                $service_id,
                $level,
                self::PENDING, //status of approval_log
                self::EH,
                self::APPROVED, // status to update or new status
                $remark,
                $date_now
            );

            if($update_equipment_handling && $update_log_approval){
                /** Create Initial Approver Logs */
                $this->approvalService->logApproval(
                    $service_id,
                    $next_approver,
                    self::EH
                );
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'external_request' => $request_type,
                // 'area' => $getInstitutionArea,
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
     * DisApprove Work Order Request (level remains the same but the status would become disapproved)
     */
    public function disapprove_request(Request $request)
    {
        $service_id = $request->service_id;
        $remark = $request->remark;
        $level = EH::where('id', $service_id)->value('level');


        try {
            DB::beginTransaction();

            $q = EH::find($service_id);
            $update_main_status = $q->update([
                    'main_status' => EH::DISAPPROVED,
                    'updated_at' => Carbon::now(),
                ]);
            if (!$update_main_status) {
                throw new Exception('Error. Something went wrong in updating.');
            }

            $logApproval = $this->approvalService->updateLogApproval(
                $service_id,
                $level,
                self::PENDING, //status of approval_log
                self::EH,
                self::DISAPPROVED, // status to update or new status
                $remark,
                Carbon::now()
            );
            if (!$logApproval) {
                throw new Exception('Failed to log approval details');
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
