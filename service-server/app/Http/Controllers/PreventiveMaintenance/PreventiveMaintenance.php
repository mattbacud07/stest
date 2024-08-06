<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Traits\GlobalVariables;
use App\Traits\Maintenance;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenance extends Controller
{
    use Maintenance, GlobalVariables;

    /** Get Preventive Mantenance Schedule */
    public function get_preventive_maintenance(Request $request, Guard $guard){
        $userArea = $guard->user()->area;
        $area = 0;
        try {
            //initialization
            $query = PM::query();

            $query->leftJoin('equipment_handling as eh', 'preventive_maintenance.service_id','=','eh.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'eh.institution', '=', 'i.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'preventive_maintenance.item_id', '=', 'm.id')
            ->leftjoin('pm_setting as p', 'preventive_maintenance.item_id', '=', 'p.equipment')
            ->leftjoin('equipment_peripherals as e', 'preventive_maintenance.service_id', '=', 'e.service_id')->where(['e.category'=>'Equipment'])
            ->select('preventive_maintenance.*','eh.institution', 'i.name', 'i.address', 'i.area','m.item_code','m.description','p.schedule','e.serial_number as serial');
            
            
            /** Flexible Condition for Specific request */
            if($request->groupByItemId){
                $query->groupBy('preventive_maintenance.service_id','preventive_maintenance.item_id');
            }
            
            if($request->engineerAccept){
                $area = self::institutionAreaToWords($userArea);
                $query->where('i.area', $area);
                $query->where('preventive_maintenance.status', PM::PendingAcceptance);
                $query->groupBy('preventive_maintenance.service_id','preventive_maintenance.item_id');
            }

            if($request->has('user_id')){
                $query->where('user_id', $request->user_id);
            }
            $get_pm_data = $query->get();
            if(!$get_pm_data){
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'pm_data' => $get_pm_data,
                'area' => $area,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }

    }

    /** Get Preventive Maintenance - sent to Engineers */
    public function get_preventive_maintenance_engineer(Request $request){
        $user_id = $request->user_id;

        try {
            $get_pm_data = PM::where('status', PM::PendingAcceptance)->get();

            if(!$get_pm_data){
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'pm_data' => $get_pm_data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }

    }


    /** Get PM - specific record */
    public function get_pm_record_details(Request $request){
        $id = $request->id;

        try {
            $get_record = PM::where('id', $id)->get();

            if(!$get_record){
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'pm_details' => $get_record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    /** Create PM */
    public function create_preventive_maintenance(Request $request){
        $user_id = $request->user_id;
        $serial_number = $request->serial_number;

        try {
            DB::beginTransaction();

            $create_pm = PM::create([
                'user_id' => $user_id,
                'serial_number' => $serial_number,
                'status' => PM::PendingAcceptance,
            ]);
            if(!$create_pm){
                throw new Exception('Error inserting');
            }
            DB::commit();
            return response()->json([
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

}
