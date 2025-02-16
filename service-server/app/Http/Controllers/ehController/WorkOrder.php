<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel as EH;
use App\Traits\GlobalVariables;
use App\Traits\InfoMessage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ApprovalService;


class WorkOrder extends Controller
{
    use GlobalVariables;
    use InfoMessage;

    /**
     * Submit Work Order Form.
     */
    public function submit_work_order(Request $request)
    {
        $approval_service = new ApprovalService();
        try {
            DB::beginTransaction();

            $peripherals = $request->peripherals;
            $equipments = $request->equipments;
            if (!empty($peripherals) && is_array($peripherals)) $level = EH::IT_DEPARTMENT;
            else $level = EH::SM_SER;

            $user_id = Auth::user()->id;

            $data =  [
                'requested_by' => $user_id,
                'institution' => $request->institution['institution_id'] ?? 0,
                'satellite' => $request->satellite ?? '',
                'proposed_delivery_date' => $request->proposed_delivery_date,
                'with_contract' => $request->with_contract,
                'attach_gate' => $request->attached_gate,
                'ship' => $request->ship,
                'bypass' => $request->bypass,
                'occular' => $request->ocular,
                'request_type' => $request->request_type,
                'other' => $request->other,
                'endorsement' => $request->endorsement,
                'level' => $level,
                'main_status' => EH::ONGOING,
            ];


            // $submittedData = DB::table('equipment_handling')->insertGetId($data);
            $submittedData = EH::create($data);
            $service_id = $submittedData->id;
            if(!$submittedData) throw new Exception($this->getMessage('ERROR','DB_INSERT'));

            /** Submit Equipments */
            $category_equipment = 'Equipment';
            $category_peripheral = 'Peripheral';


            $this->submit_equipments($equipments, $service_id, $category_equipment, self::EH);
            $this->submit_equipments($peripherals, $service_id, $category_peripheral, self::EH);
            
            /** Create Initial Approver Logs */
            $approval_service->logApproval($service_id, $level, self::EH);

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }



    public function submit_equipments($equipmentArray, $service_id, $category, $request_type){
        $equipment_collections = collect($equipmentArray)
            ->map(function($equipmentArray) use($service_id, $category, $request_type)
            {
            return [
                'service_id' => $service_id,
                'item_id' => $equipmentArray['item_id'],
                'service_master_data_id' => $equipmentArray['service_master_data_id'] ?? null,
                'serial_number' => $equipmentArray['serial'] ?? '',
                'remarks' => $equipmentArray['remarks'],
                'category' => $category,
                'request_type' => $request_type,
                'status' => 'Active',
            ];
        })->all();

        $insert_equipment = DB::table('equipment_peripherals')->insert($equipment_collections);

        if(!$insert_equipment){
            throw new Exception('Error adding to the database, '. $category .' batch file');
        }
    }
}
