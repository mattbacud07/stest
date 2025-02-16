<?php

namespace App\Services;

use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
use Exception;
use Illuminate\Support\Facades\Auth;

class TaskDelegationService
{
    public function task_delegation_log($service_id, $assigned_to, $assigned_by, $status, $type, $remarks, $acted_at){

        $data = [
            'service_id' => $service_id,
            'assigned_to' => $assigned_to,
            'assigned_by' => $assigned_by,
            'status' => $status,
            'active' => 1,
            'type' => $type,
            'remarks' => $remarks,
            'acted_at' => $acted_at,
        ];

        $query = EngineerTaskDelegation::create($data);

        if(!$query){
            throw new Exception('Error inserting task log');
        }
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