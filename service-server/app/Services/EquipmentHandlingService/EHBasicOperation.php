<?php

namespace App\Services\EquipmentHandlingService;

use App\Models\EhServicesModel;
use Illuminate\Support\Facades\DB;

class EHBasicOperation
{
    /**
     * Get a specific equipment handling by its ID.
     * 
     * @param int $id
     * @return \App\Models\EhServicesModel|null
     */
    // public function getEquipmentHandlingByServiceId($service_id)
    // {
    //     // Find and return an equipment handling by its ID, or return null if not found
    //     $service_data = EhServicesModel::select(
    //         'equipment_handling.*', 
    //         'i.name', 'i.address', 
    //         'i.area', 'u.first_name', 
    //         'u.last_name', 'u.department', 
    //         'ui.first_name as fname', 
    //         'ui.last_name as lname', 
    //         'ua.first_name as assigned_tl_fname', 
    //         'ua.last_name as assigned_tl_lname', 
    //         'iE.name as request_name')
    //         ->where(['equipment_handling.id' => $service_id])
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ui', 'equipment_handling.installer', '=', 'ui.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ua', 'equipment_handling.tl_assigned', '=', 'ua.id')
    //         ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
    //         ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
    //         // ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
    //         // ->leftjoin('approver_designation as a', 'equipment_handling.level', '=', 'a.approver_level')
    //         ->get();

    //         return $service_data;
    // }


    /**
     * Delete an equipment handling by its ID
     */
    public function deleteEquipmentHandling($id)
    {
        $equipmentHandling = EhServicesModel::find($id);
        if ($equipmentHandling) {
            $equipmentHandling->delete();
            return true;
        }
        return false;
    }


    
    /**
     * Get equipment handlings based on a specific equipment ID.
     */
    public function getEquipmentHandlingsByEquipmentId($equipmentId)
    {
        // Retrieve and return equipment handlings associated with the given equipment ID
        return EhServicesModel::where('equipment_id', $equipmentId)->get();
    }
}
