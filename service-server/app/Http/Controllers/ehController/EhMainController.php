<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EhMainController extends Controller
{
    public function index(Request $request)
    {
        $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approval_level', 'a.approval_level_name')
        ->where(['requested_by' => $request->input('user_id')])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
        ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
        ->leftjoin('approval_configuration as a', 'equipment_handling.status', '=', 'a.approval_level')
        ->get();

        return response()->json([
            'dataItems' => $data,
        ], 200);
    }

    /**
     * Submit Work Order Form.
     */
    public function addWorkOrder()
    {
        $equipments = DB::connection('mysqlSecond')->table('master_data')->get();
        $institutions = DB::connection('mysqlSecond')->table('mt_bp_institutions')->get();

        return response()->json([
            'equipments' => $equipments,
            'institutions' => $institutions,
        ]);
    }

    /**
     * Work Order Approval Form.
     */
    public function work_order_approval_form()
    {
        $equipments = DB::connection('mysqlSecond')->table('master_data')->get();
        $institutions = DB::connection('mysqlSecond')->table('mt_bp_institutions')->get();

        return view('pages.EquipmentHandling.eh-WorkOrderApproval', [
            'equipments' => $equipments,
            'institutions' => $institutions,
        ]);
    }
}
