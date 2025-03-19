<?php

namespace App\Console\Commands\PM;

use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutomatePM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-pm-status-to-ready';

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
            $dateToday = Carbon::now(); // Example: March 7, 2024
            $getScheduledRecords = PM::where('status', PM::Scheduled)->get();
    
            foreach ($getScheduledRecords as $data) {
                $dateThreshold = Carbon::parse($data->scheduled_at)->subDays(7); // Scheduled date - 7 days
    
                if ($dateToday->greaterThanOrEqualTo($dateThreshold) && $dateToday->lessThanOrEqualTo($data->scheduled_at)) {
                    $data->update(['status' => PM::ReadyForDelegation]);
                }

                // SEND EMAIL FOR SBU's Head and Assistant = to SBU assigned to Machine 
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}
