<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
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

    public function __construct(TaskDelegationService $task_log)
    {
        $this->task_log = $task_log;
    }

    public function checkIfRequestExist($service_id){
        $requestExist = EngineerTaskDelegation::where([
            'service_id' => $service_id,
            'type' => self::EH,
            'active' => 1,
        ])->first();
        if($requestExist){
            return response()->json(['exist' => true]);
        }
    }

    public function delegate_engineer(Request $request){
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
    
    
    
    
    public function accept_decline_delegate(Request $request){
        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        $delegation_id = $request->delegation_id;
        $status = $request->status; // Accept or Decline
        $remarks = $request->remarks;
        try {
            DB::beginTransaction();

            $this->checkIfRequestExist($request->service_id);

            if($status == 'accepted'){
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
            }else{
                $update_delegation_status = $this->task_log->update_task_delegation_log(
                    $service_id,
                    self::DELEGATED,
                    self::EH,
                    self::DECLINED,
                    $remarks,
                    0 // set active status to 0
                );
    
                if (!$update_delegation_status) throw new Exception('Failed to update delegation status');
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
}
