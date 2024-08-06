<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class WorkOrder extends Controller
{
    use GlobalVariables;

    /**
     * Submit Work Order Form.
     */
    public function submit_work_order(Request $request)
    {
        $peripherals = $request->peripherals;
        $equipments = $request->equipments;
        if (!empty($peripherals) && is_array($peripherals)) {
            $status = self::IT_DEPARTMENT;
        } else {
            $status = self::APM_NSM_SM;
        }
        $data =  [
            'requested_by' => $request->requested_by,
            'institution' => $request->institution,
            'address' => $request->institution,
            'proposed_delivery_date' => $request->proposed_delivery_date,
            'with_contract' => $request->with_contract,
            'attach_gate' => $request->attach_gate,
            'ship' => $request->ship,
            'bypass' => $request->bypass,
            'occular' => $request->ocular,
            'request_type' => $request->request_type,
            'other' => $request->other,
            // 'external_request' => $request->externalRequest,
            // 'internal_request' => $request->internalRequest,
            'endorsement' => $request->endorsement,
            'status' => $status,
            'main_status' => self::ONGOING,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        try {
            DB::beginTransaction();

        
            $submitData = DB::table('equipment_handling')->insertGetId($data);
            if ($submitData) {
                /** Equipments Data */
                $data_equipments = collect($equipments)->map( function($equipments) use($submitData) {
                    return [
                        'service_id' => $submitData,
                        'item_id' => $equipments['id'],
                        'description' => $equipments['description'],
                        'serial_number' => $equipments['equipmentSerial'],
                        'remarks' => $equipments['equipmentRemark'],
                        'category' => 'Equipment',
                        'status' => 'Active',
                    ];
                })->all();
                $insert_equipment = DB::table('equipment_peripherals')->insert($data_equipments);
                
                /** Peripherals Data */
                $data_peripheral = collect($peripherals)->map( function($peripherals) use($submitData) {
                    return [
                        'service_id' => $submitData,
                        'item_id' => $peripherals['id'],
                        'description' => $peripherals['description'],
                        'serial_number' => '',
                        'remarks' => $peripherals['peripheralRemark'],
                        'category' => 'Peripheral',
                        'status' => 'Active',
                    ];
                })->all();
                $insert_peripheral = DB::table('equipment_peripherals')->insert($data_peripheral);

                if($insert_equipment && $insert_peripheral){
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'equipments' => $equipments[0],
                    ], 200);
                }else{
                    return response()->json([
                        'isError' => 'Something went wrong inserting batch file',
                    ], 500);
                    throw new Exception('Error adding to the database, batch file');
                }
            } else {
                // return response()->json([
                //     'isError' => 'Something went wrong',
                // ], 200);
                throw new Exception('Error adding to the database');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'isError' => $e->getMessage(),
            ]);
        }

            
    }
}
