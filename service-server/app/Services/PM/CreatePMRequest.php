<?php

namespace app\Services\PM;

use App\Models\PreventiveMaintenance\PMActions;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Illuminate\Support\Facades\DB;

class CreatePMRequest
{
    public function create_request($data, $complaints, $problems, $work_type)
    {
        DB::beginTransaction();
        try {
            $get_id = PM::create($data)->id;

            $actions = [];

            foreach ($complaints as $complaint) {
                $actions[] = [
                    'maintenance_id' => $get_id,
                    'actions' => $complaint,
                    'type' => PMActions::complaint,
                    'work_type' => $work_type,
                ];
            }

            foreach ($problems as $problem) {
                $actions[] = [
                    'maintenance_id' => $get_id,
                    'actions' => $problem,
                    'type' => PMActions::problem,
                    'work_type' => $work_type,
                ];
            }

            // Perform a bulk insert for actions
            if (!empty($actions)) {
                PMActions::insert($actions);
            }
            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
