<?php

namespace App\Services\EquipmentHandlingService;

use App\Models\EhServicesModel;
use App\Traits\GlobalVariables;
use Illuminate\Support\Facades\DB;

class EHTaskOperation
{
    use GlobalVariables;
    /**
     * Get Equipment handling by USER ID and Based on WHERE STatement.
     */
    public function getEquipmentHandlingByUserId($user_id, $what_to_get)
    {
        $getApprovalLevel = DB::table('approval_configuration')->where('user_id', $user_id)->first();
        $approval_level = $getApprovalLevel->approval_level ?? 0;
        
        $query = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'iE.name as request_name','a.approver_level', 'a.approver_name')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
        ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level');

        if($what_to_get === self::approverRole){
            $query->where(['equipment_handling.status' => $approval_level, 'equipment_handling.main_status' => self::ONGOING]);
        }else{
            $query->where('requested_by', $user_id);
        }

        $data = $query->get();

        return $data;
    }


    /**
     * Equipment Handling - Approve Request
     */
    public function approveEquipmentHandlingRequest(){

    }
}
