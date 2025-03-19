<?php

namespace App\Services;

use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class TaskDelegationService
{
    public function task_delegation_log($service_id, $assigned_to =null, $assigned_by = null, $status, $type, $remarks = null, $acted_at = null){

        $data = array_filter([
            'service_id' => $service_id,
            'assigned_to' => $assigned_to,
            'assigned_by' => $assigned_by,
            'status' => $status,
            'active' => 1,
            'type' => $type,
            'remarks' => $remarks,
            'acted_at' => $acted_at,
        ], fn($value) => !is_null($value));

        $query = EngineerTaskDelegation::create($data);

        if(!$query){
            throw new Exception('Error inserting task log');
        }

        return $query;
    }
    
    
    public function update_task_delegation_log($internal_id,$status, $type, $new_status, $remarks = null, $new_active = null)
    {
        return EngineerTaskDelegation::where([
            'service_id' => $internal_id,
            'status' => $status,
            'type' => $type,
            'active' => 1, //// subject to change if nenecessary
        ])->first()?->update(array_filter([
            'status' => $new_status,
            'remarks' => $remarks,
            'active' => $new_active,
            'acted_at' => Carbon::now(),
        ], fn($value) => !is_null($value)));
    }
    





    /** Task Service Engineer Activities */
    public function task_activities($delegation_id, $status, $acted_at, $type){
        $data = [
            'service_id' => $delegation_id,
            'status' => $status,
            'acted_at' => $acted_at,
            'type' => $type,
        ];

        $q = EngineerActivities::create($data);
        if(!$q){
            throw new Exception('Error inserting activity log');
        }
    }
}