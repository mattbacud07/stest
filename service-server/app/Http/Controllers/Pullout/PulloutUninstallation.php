<?php

namespace App\Http\Controllers\Pullout;

use App\Http\Controllers\Controller;
use App\Models\EngineerTaskDelegation;
use App\Services\ActionsDoneService;
use App\Services\TaskDelegationService;
use App\Traits\GlobalVariables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PulloutUninstallation extends Controller
{

    public function checkIfRequestExist($service_id) {
        $requestExist = EngineerTaskDelegation:: where([
            'service_id' => $service_id,
            'type' => self:: EH,
            'active' => 1,
        ]) -> first();
        if ($requestExist) {
            return response() -> json(['exist' => true]);
        }
    }



    use GlobalVariables;
    protected $task_log;
    protected $action;
    public function __construct(
        TaskDelegationService $task_log,
        ActionsDoneService $actions,
    ) {
        $this->action = $actions;
        $this->task_log = $task_log;
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
                self::PULLOUT,
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
