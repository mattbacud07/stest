<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\CorrectiveMaintenance\CorrectiveMaintenance;
use App\Models\EngineerTaskDelegation;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendToCM extends Controller
{
    public function sendToCM(Request $request)
    {
        $id = $request->id ?? 0;
        $pm_id = $request->pm_id ?? 0;
        $module = $request->module ?? null;

        try {
            DB::beginTransaction();

            /** Generate another Schedule after this submitted */
            $task_delegation = EngineerTaskDelegation::where('id', $id)->first();
            $tag = null;

            if($task_delegation->tag != PM::pm_tag_under_observation){
                throw new Exception('Tag is not for update');
            }
            if ($task_delegation->status_after_service == PM::operational) $tag = PM::pm_tag_backjob;
            else if ($task_delegation->status_after_service == PM::further_monitoring) $tag = PM::pm_tag_non_operational;
            else $tag = PM::pm_tag_backlog;

            EngineerTaskDelegation::where('id', $id)->update([
                'tag' => $tag,
            ]);

            $data = $module == 'cm' ? CorrectiveMaintenance::find($pm_id) : PM::find($pm_id);

            CorrectiveMaintenance::create([
                'equipment_peripheral_id' => $data['equipment_peripheral_id'],
                'service_id' => $data['service_id'],
                'item_id' => $data['item_id'],
                'serial' => $data['serial'],
                'institution' => $data['institution'],
                // 'date_installed' => $data['date_installed'],
                'status' => PM::ReadyForDelegation,
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




    /** Set Days of Observation */
    public function setDaysObservation(Request $request)
    {
        $id = $request->id ?? 0;
        $set_days = $request->set_days ?? 0;

        try {
            DB::beginTransaction();
            $setDaysToObserve = Carbon::now()->startOfDay()->addDays($set_days);

            $update = EngineerTaskDelegation::find($id);
            $update->update([
                'monitoring_end' => $setDaysToObserve,
                'tag' => PM::pm_tag_under_observation,
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
