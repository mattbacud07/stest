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
    protected $signature = 'app:PMUpdateStatus';

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
        $dateToday = Carbon::now();
        try {
            DB::beginTransaction();
            $getScheduledToday = PM::whereDate('scheduled_at', '=', $dateToday)->where('status', PM::Scheduled)->get();
            foreach ($getScheduledToday as $data) {
                $data->update(['status' => PM::PendingAcceptance]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}
