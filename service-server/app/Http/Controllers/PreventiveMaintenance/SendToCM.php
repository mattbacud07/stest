<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendToCM extends Controller
{
    public function sendToCM(Request $request){
        $id = $request->pm_id ?? 0;

        try {
            DB::beginTransaction();

        /** Generate another Schedule after this submitted */
         $pm_data = PM::where('id', $id)->first();
        $tag = null;
         if($pm_data->status_after_service == PM::operational) $tag = PM::pm_tag_backjob;
         else if($pm_data->status_after_service == PM::further_monitoring) $tag = PM::pm_tag_non_operational;
         else $tag = PM::pm_tag_backlog;

         PM::where('id', $pm_data->id)->update([
            'tag' => $tag,
         ]);

         PM::create([
             'equipment_peripheral_id' => $pm_data['equipment_peripheral_id'],
             'service_id' => $pm_data['service_id'],
             'item_id' => $pm_data['item_id'],
             'serial' => $pm_data['serial'],
             'institution' => $pm_data['institution'],
             'ssu' => $pm_data['ssu'],
             'date_installed' => $pm_data['date_installed'],
             'status' => PM::Scheduled,
             'work_type' => 'CM',
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
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
    public function setDaysObservation(Request $request){
        $id = $request->id ?? 0;
        $set_days = $request->set_days ?? 0;

        try {
            DB::beginTransaction();
            $getDateObservation = Carbon::now()->startOfDay()->addDays($set_days);

            $update = PM::find($id);
            $update->update([
                'monitoring_end' => $getDateObservation,
                'tag' => PM::pm_tag_under_observation,
            ]);
            DB::commit();
        return response()->json([
            'success' => $update,
        ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }
}
