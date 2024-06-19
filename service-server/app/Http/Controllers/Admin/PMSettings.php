<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PMSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PMSettings extends Controller
{
    /** Set PM Schedule [monthly, quarterly, semi-annually, annually] */
    public function pm_schedule(Request $request){
        $equipment_id = $request->equipmentId;
        $pmSched = $request->pmSched;

        DB::beginTransaction();
        try {
            $checkSched = DB::table('pm_setting')->where('equipment', $equipment_id)->exists();
            if($checkSched){
                return response()->json(['set' => true, ]);
            }else{
                $saveSched = DB::table('pm_setting')->insert([
                    'equipment' => $equipment_id,
                    'schedule' => $pmSched,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                if(!$saveSched){
                    throw new Exception('Error in inserting');
                }
                DB::commit();
                return response()->json(['success' => true, ]);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage(), ]);
        }
    }
    
    
    
    /** Edit PM Schedule [monthly, quarterly, semi-annually, annually] */
    public function edit_pm_schedule(Request $request){
        $id = $request->id;
        $pmSched = $request->pmSched;

        DB::beginTransaction();
        try {
                $saveSched = DB::table('pm_setting')->where('id', $id)->update([
                    'schedule' => $pmSched,
                    'updated_at' => Carbon::now(),
                ]);
                if(!$saveSched){
                    throw new Exception('Error in updating');
                }
                DB::commit();
                return response()->json(['success' => true, ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage(), ]);
        }
    }


    /** Get all equipment-set PM Schedule */
    public function get_pm_schedule(){
        $equipments = PMSetting::select('pm_setting.*', 'm.item_code','m.description')
                        ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.master_data as m', 'pm_setting.equipment','=','m.id')
                        ->get();

        return response()->json([
            'equipments_set_sched' => $equipments,
        ]);
    }
}
