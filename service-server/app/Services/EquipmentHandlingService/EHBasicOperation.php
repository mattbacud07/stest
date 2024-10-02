<?php

namespace App\Services\EquipmentHandlingService;

use App\Models\EhServicesModel;
use Illuminate\Support\Facades\DB;

class EHBasicOperation
{
    /**
     * Get all equipment handlings.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllEquipmentHandlings()
    {
        // Retrieve and return all equipment handlings from the database
        $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'iE.name as request_name','a.approver_level', 'a.approver_name')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
        ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
        ->get();

        return $data;
    }

    /**
     * Get a specific equipment handling by its ID.
     * 
     * @param int $id
     * @return \App\Models\EhServicesModel|null
     */
    public function getEquipmentHandlingByServiceId($service_id)
    {
        // Find and return an equipment handling by its ID, or return null if not found
        $service_data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'i.area', 'u.first_name', 'u.last_name', 'ui.first_name as fname', 'ui.last_name as lname', 'ua.first_name as assigned_tl_fname', 'ua.last_name as assigned_tl_lname', 'iE.name as request_name', 'a.approver_level', 'a.approver_name')
            ->where(['equipment_handling.id' => $service_id])
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ui', 'equipment_handling.installer', '=', 'ui.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ua', 'equipment_handling.tl_assigned', '=', 'ua.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
            ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
            // ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
            ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
            ->get();

            return $service_data;
    }


    /**
     * Delete an equipment handling by its ID.
     * 
     * @param int $id
     * @return bool
     */
    public function deleteEquipmentHandling($id)
    {
        // Find the equipment handling by its ID
        $equipmentHandling = EhServicesModel::find($id);
        if ($equipmentHandling) {
            // Delete the equipment handling
            $equipmentHandling->delete();
            // Return true indicating success
            return true;
        }
        // Return false if the equipment handling was not found
        return false;
    }


    
    /**
     * Get equipment handlings based on a specific equipment ID.
     * 
     * @param int $equipmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEquipmentHandlingsByEquipmentId($equipmentId)
    {
        // Retrieve and return equipment handlings associated with the given equipment ID
        return EhServicesModel::where('equipment_id', $equipmentId)->get();
    }
}
