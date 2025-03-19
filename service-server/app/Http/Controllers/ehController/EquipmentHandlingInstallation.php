<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
use App\Models\MasterData;
use App\Models\ServiceMasterData;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Services\ActionsDoneService;
use App\Services\PM\GeneratePMSched;
use App\Services\TaskDelegationService;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentHandlingInstallation extends Controller
{
    use GlobalVariables;
    protected $task_log;
    protected $action;
    protected $calculate_sched;

    public function __construct(
        TaskDelegationService $task_log,
        ActionsDoneService $actions,
        GeneratePMSched $generate_sched
    ) {
        $this->action = $actions;
        $this->task_log = $task_log;
        $this->calculate_sched = $generate_sched;
    }

    public function checkIfRequestExist($service_id)
    {
        $requestExist = EngineerTaskDelegation::where([
            'service_id' => $service_id,
            'type' => self::EH,
            'active' => 1,
        ])->first();
        if ($requestExist) {
            return response()->json(['exist' => true]);
        }
    }

    public function delegate_engineer(Request $request)
    {
        $user_id = Auth::user()->id;
        try {
            DB::beginTransaction();
            $this->checkIfRequestExist($request->service_id);

            $this->task_log->task_delegation_log(
                $request->service_id,
                $request->assigned_to,
                $user_id,
                self::DELEGATED,
                self::EH,
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }




    public function accept_decline_delegate(Request $request)
    {
        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        $delegation_id = $request->delegation_id;
        $status = $request->status; // Accept or Decline
        $remarks = $request->remarks;
        try {
            DB::beginTransaction();

            $this->checkIfRequestExist($request->service_id);

            if ($status == 'accepted') {
                $update_delegation_status = $this->task_log->update_task_delegation_log(
                    $service_id,
                    self::DELEGATED,
                    self::EH,
                    self::ACCEPTED,
                    $remarks
                );

                if (!$update_delegation_status) throw new Exception('Failed to accept delegation status');

                /** Create Task Log for Task Delegation */
                $this->task_log->task_activities(
                    $delegation_id,
                    EngineerActivities::Started,
                    Carbon::now(),
                    self::EH
                );
            } else {
                $update_delegation_status = $this->task_log->update_task_delegation_log(
                    $service_id,
                    self::DELEGATED,
                    self::EH,
                    self::DECLINED,
                    $remarks,
                    0 // set active status to 0
                );

                if (!$update_delegation_status) throw new Exception('Failed to update delegation status. Accepted already.');
            }

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }



    public function mark_as_completed(Request $request)
    {
        $status_after_service =  $request->status_after_service;
        $actions_taken =  $request->fields;
        $spareparts =  $request->spareparts;
        $remarks =  $request->remarks;
        $complaint =  $request->complaint ?? '';
        $problem =  $request->problem ?? '';
        $delegation_id =  $request->delegation_id;
        $id =  $request->id;
        $date_now = Carbon::now();
        try {
            DB::beginTransaction();

            $this->checkIfRequestExist($id);

            /** Get all the Equipments for automation of PM Sched */
            $equipments = EquipmentPeripherals::select('equipment_peripherals.*', 'smd.serial', 'smd.frequency', 'eh.institution as ehInstitution')
                ->leftJoin('service_master_data as smd', 'equipment_peripherals.service_master_data_id', '=', 'smd.id')
                ->leftJoin('equipment_handling as eh', 'equipment_peripherals.service_id', '=', 'eh.id')
                ->where('equipment_peripherals.service_id', $id)
                ->where('equipment_peripherals.category', 'Equipment')
                ->where('equipment_peripherals.request_type', self::EH)
                ->get();

            $request_type = EhServicesModel::where('id', $id)->value('request_type'); // Get External Option Request for specific generation of auto PM


            /** Get External Request Option */
            $externalRequestOptionArray = [self::REAGENT, self::PURCHASED]; //this is  accrding to SDpt (Sir Cloy)

            if ($request_type && in_array($request_type, $externalRequestOptionArray)) {
                $this->calculate_sched->calculateNextPMSchedule($date_now, $equipments);
            }


            /** Actions Taken */
            $dataToInsert = [];
            foreach ($actions_taken as $action) {
                $dataToInsert[] = [
                    'service_id' => $delegation_id,
                    'action' => $action['action'],
                    'work_type' => self::EH
                ];
            }
            $this->action->declare_actions_done($dataToInsert);
            
            
            /** Spareparts Used */
            $sparePartsToInsert = [];
            foreach ($spareparts as $parts) {
                $sparePartsToInsert[] = [
                    'service_id' => $delegation_id,
                    'item_id' => $parts['item_id'],
                    'qty' => $parts['qty'],
                    'dr' => $parts['dr'],
                    'si' => $parts['si'],
                    'remarks' => $parts['remarks'],
                    'type' => self::EH
                ];
            }
            $this->action->declare_spareparts_used($sparePartsToInsert);


            /** Update Status to Complete */
            EhServicesModel::where('id', $id)->update([
                'level' => EhServicesModel::EH_SIGNATORY_COMPLETE,
                'main_status' => EhServicesModel::COMPLETE,
            ]);

            /** Update Task Delegation to Complete */
            $update_delegation = EngineerTaskDelegation::where('id', $delegation_id)->first()?->update([
                'status_after_service' => $status_after_service,
                'complaint' => $complaint,
                'problem' => $problem,
                'sr_remarks' => $remarks,
                'status' => self::COMPLETED,
            ]);
            if (!$update_delegation) {
                throw new Exception('Error updating delegation');
            }

            /** Update Master Data - Institution and Status*/
            foreach($equipments as $equipment){
                $machine = ServiceMasterData::find($equipment->service_master_data_id);
                $machine->update([
                    'institution' => $equipment->ehInstitution,
                    'status' => 'Active'
                ]);
            }

            /** Log Engineer Activities */
            $this->task_log->task_activities(
                $delegation_id,
                EngineerActivities::Ended,
                Carbon::now(),
                self::EH
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
