<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EhMainApproverController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = $request->user_id ?? 0;
        $getApprovalLevel = DB::table('approval_configuration')->where('user_id', $currentUserId)->first();
        $approval_level = $getApprovalLevel->approval_level ?? 0;
        
        $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approval_level', 'a.approval_level_name')
        ->where(['equipment_handling.status' => $approval_level])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
        ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
        ->leftjoin('approval_configuration as a', 'equipment_handling.status', '=', 'a.approval_level')
        ->get();
        
        
        $service_id = $request->service_id ?? 0;
        $service_data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approval_level', 'a.approval_level_name')
        ->where(['equipment_handling.id' => $service_id])
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
        ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
        ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
        ->leftjoin('approval_configuration as a', 'equipment_handling.status', '=', 'a.approval_level')
        ->get();

        return response()->json([
            'workOrder' => $data,
            'serviceData' => $service_data,
        ], 200);
    }
}
