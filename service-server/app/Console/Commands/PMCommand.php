<?php

namespace App\Console\Commands;

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
    protected $signature = 'app:monitor-pm';

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
            $get_expires_end_of_monitoring = PM::whereIn('status_after_service', $afterService)
                ->where('tag', PM::pm_tag_under_observation)
                ->get();
            foreach ($get_expires_end_of_monitoring as $dataMonitoring) {
                if ($dataMonitoring['monitoring_end'] < $today) {
                    PM::where('id', $dataMonitoring['id'])->update([
                        'status' => PM::Completed,
                        'tag' => PM::pm_tag_completed
                    ]);
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
