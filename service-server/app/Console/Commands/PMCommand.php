<?php

namespace App\Console\Commands;

use App\Models\EngineerTaskDelegation;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PMCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-pm-cm-after-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            /**Start Command here */
            $today = Carbon::today();

            $afterService = [PM::operational, PM::further_monitoring];
            $get_expires_end_of_monitoring = EngineerTaskDelegation::whereIn('status_after_service', $afterService)
                ->where('tag', PM::pm_tag_under_observation) //only PM and CM have this column
                ->where('active', 1)
                ->get();
            foreach ($get_expires_end_of_monitoring as $dataMonitoring) {
                if (Carbon::parse($dataMonitoring['monitoring_end'])->lessThanOrEqualTo($today)) {
                    EngineerTaskDelegation::where('id', (int) $dataMonitoring['id'])->update([
                        'status' => PM::Completed,
                        'tag' => PM::pm_tag_completed
                    ]);

                    //SEND EMAIL TO HEAD(Assigned by) AND ENGINEER(Assigned_to) OF THE RECORD
                    //PUT HERE ===============>
                }
            }

            DB::commit();
            return response()->json([
                'success' => 'Successfully updated status' 
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }
}
