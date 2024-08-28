<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PMSetting;
use App\Models\EhServicesModel;
use App\Models\PreventiveMaintenance\PMOptionsAction;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Services\PM\GeneratePMSched;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PMSettings extends Controller
{
    protected $generatePMSched;
    public function __construct(GeneratePMSched $generatePMSched)
    {
        $this->generatePMSched = $generatePMSched;
    }

    /** Set PM Schedule [monthly, quarterly, semi-annually, annually] */
    public function pm_schedule(Request $request)
    {
        $equipment_id = $request->equipmentId;
        $frequency = $request->frequency;
        $expiration_date = Carbon::now()->endOfYear();

        DB::beginTransaction();
        try {
            $checkSched = DB::table('pm_setting')->where('equipment', $equipment_id)->exists();
            if ($checkSched) {
                return response()->json(['set' => true,]);
            } else {
                $saveSched = DB::table('pm_setting')->insert([
                    'equipment' => $equipment_id,
                    'schedule' => $frequency,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);


                /** Get Specific Items - Scheduled Status is not Set */
                $getNotSetScheduled = PM::where([
                    'item_id' => $equipment_id,
                    'status' => PM::NotSet,
                ])->get();
                foreach ($getNotSetScheduled as $item) {
                    $dateInstalled = Carbon::parse($item['date_installed']);

                    $scheduled_at = $this->generatePMSched->calculateSchedFrequency($dateInstalled, $frequency);
                    $allSched = $this->generatePMSched->calculateAllSchedFrequency($dateInstalled, $frequency, $expiration_date);
                    
                    PM::where([
                        'id' => $item['id'],
                        'status' => $item['status']
                    ])->update([
                        'list_scheduled' => $allSched,
                        'scheduled_at' => $scheduled_at,
                        'status' => PM::Scheduled,
                    ]);
                }

                if (!$saveSched) {
                    throw new Exception('Error in inserting');
                }
                DB::commit();
                return response()->json([
                    'success' => true,
                    'getNotSetScheduled' => $getNotSetScheduled,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage(),]);
        }
    }



    /** Edit PM Schedule [monthly, quarterly, semi-annually, annually] */
    public function edit_pm_schedule(Request $request)
    {
        $id = $request->id;
        $frequency = $request->frequency;
        $expiration_date = Carbon::now()->endOfYear();

        DB::beginTransaction();
        try {
            $saveSched = DB::table('pm_setting')->where('id', $id)->update([
                'schedule' => $frequency,
                'updated_at' => Carbon::now(),
            ]);


            /** Get Specific Items - Scheduled Status is not Set */
            $getEquipmentId = DB::table('pm_setting')->where('id', $id)->value('equipment');
            $getNotSetScheduled = PM::where([
                'item_id' => $getEquipmentId,
                'status' => PM::NotSet,
            ])->get();
            foreach ($getNotSetScheduled as $item) {
                $dateInstalled = Carbon::parse($item['date_installed']);

                $scheduled_at = $this->generatePMSched->calculateSchedFrequency($dateInstalled, $frequency);
                $allSched = $this->generatePMSched->calculateAllSchedFrequency($dateInstalled, $frequency, $expiration_date);
                
                PM::where([
                    'id' => $item['id'],
                    'status' => $item['status']
                ])->update([
                    'list_scheduled' => $allSched,
                    'scheduled_at' => $scheduled_at,
                    'status' => PM::Scheduled,
                ]);
            }



            if (!$saveSched) {
                throw new Exception('Error in updating');
            }
            DB::commit();
            return response()->json(['success' => true,]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage(),]);
        }
    }


    /** Get all equipment-set PM Schedule */
    public function get_pm_schedule()
    {
        $equipments = PMSetting::select('pm_setting.*', 'm.item_code', 'm.description')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'pm_setting.equipment', '=', 'm.id')
            ->get();

        return response()->json([
            'equipments_set_sched' => $equipments,
        ]);
    }






    /** +++++++++++++++++++++++++++++++++++++ Standard Actions Done ++++++++++++++++++++++++++++++++ */
    public function getStandardActions(){
        try {
            DB::beginTransaction();


            $actions = PMOptionsAction::get();


            DB::commit();

            return response()->json(['actions' => $actions,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage(),], 200);
        }
    }
    
    
    
    
    public function saveAction(Request $request){
        try {
            DB::beginTransaction();

            $data = [
                'actions' => $request->actions,
            ];

            $actions = PMOptionsAction::create($data);

            if(!$actions){
                return response()->json(['error' => true,], 500);
            }
            DB::commit();

            return response()->json(['success' => true,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage(),], 200);
        }
    }


    public function deleteAction(Request $request){
        try {
            DB::beginTransaction();

            $delete = PMOptionsAction::findOrFail($request->id);
            $delete->delete();

            // if(!$actions){
            //     return response()->json(['error' => true,], 500);
            // }
            DB::commit();

            return response()->json(['success' => true,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage(),], 200);
        }
    }
    
    
    
    
    public function editActions(Request $request){
        try {
            DB::beginTransaction();

            $update = PMOptionsAction::findOrFail($request->id);
            $update->update([
                'actions' => $request->actions,
            ]);

            if(!$update){
                return response()->json(['error' => true,], 500);
            }
            DB::commit();

            return response()->json(['success' => true,], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage(),], 200);
        }
    }
}
