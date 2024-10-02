<?php

namespace App\Services\EquipmentHandlingService;

use App\Models\EhServicesModel as EH;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Traits\GlobalVariables;
use Illuminate\Support\Facades\DB;

class EHTaskOperation
{
    use GlobalVariables;
    /**
     * Get Equipment handling by USER ID and Based on WHERE STatement.
     */
    public function getEquipmentHandlingByUserId($user_id, $category, $myRequest)
    {
        $getApprovalLevel = DB::table('approval_configuration')->where('user_id', $user_id)->first();
        $getUserSSU = RoleUser::where(['user_id' => $user_id, 'role_id' => 2])->first();
        $approval_level = $getApprovalLevel->approval_level ?? 0;

        $query = EH::select('equipment_handling.*', 'i.name', 'i.address', 'i.area', 'u.first_name', 'u.last_name', 
            DB::raw("CONCAT(u.first_name,' ',u.last_name) as user_name"), 'iE.name as request_name', 'a.approver_level', 'a.approver_name')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
            ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
            ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level');
        
        $convertMyRequestToBoolean = filter_var($myRequest, FILTER_VALIDATE_BOOLEAN);
        if($convertMyRequestToBoolean){
            $query->where('requested_by', $user_id);
        }else{
            if ($category === Roles::approverRole) {
                $query->where([
                    'equipment_handling.status' => $approval_level,
                    'equipment_handling.main_status' => EH::ONGOING,
                ]);
            } elseif ($category === Roles::TLRole) {
                $query->where([
                    'equipment_handling.status' => EH::INSTALLATION_TL,
                    'equipment_handling.ssu' => $getUserSSU['SSU'],
                    'equipment_handling.main_status' => EH::ONGOING
                ]);
            }
            // elseif($category === Roles::OutboundRole){
            //     $query->where([
            //         'equipment_handling.status' => EH::OUTBOUND,
            //         'equipment_handling.main_status' => EH::ONGOING]);
            // }
            elseif ($category === Roles::engineerRole) {
                $query->where([
                    'equipment_handling.installer' => $user_id,
                    'equipment_handling.status' => EH::INSTALLATION_ENGINEER,
                    'equipment_handling.main_status' => EH::INSTALLING
                ]);
            } else {
                $query->where('requested_by', $user_id);
            }
        }

        $data = $query->get();
        $count = $query->count();

        // Return as an array
        return [
            'data' => $data,
            'count' => $count
        ];
    }
}
