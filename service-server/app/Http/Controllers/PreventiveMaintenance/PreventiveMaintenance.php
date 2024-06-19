<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Traits\Maintenance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenance extends Controller
{
    use Maintenance;
    /** Get Preventive Mantenance - specific user */
    public function get_preventive_maintenance(Request $request){
        $user_id = $request->user_id;

        try {
            $get_pm_data = PM::where('user_id', $user_id)->get();

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

    /** Get Preventive Maintenance - sent to Engineers */
    public function get_preventive_maintenance_engineer(Request $request){
        $user_id = $request->user_id;

        try {
            $get_pm_data = PM::where('status', self::waiting_engineer)->get();

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
                'status' => self::waiting_engineer,
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
