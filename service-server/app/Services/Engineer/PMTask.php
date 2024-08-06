<?php

namespace App\Services\Engineer;

use App\Models\PreventiveMaintenance\PMSchedule;
use Exception;
use Illuminate\Support\Facades\Request;

class PMTask
{
    public function accept_pm_task($id, $user_id){
            $accept = PMSchedule::create([
                'pm_id' => $id,
                'user_id' => $user_id,
            ]);
            if(!$accept){
                throw new Exception('Error in inserting task');
            }
    }
}
