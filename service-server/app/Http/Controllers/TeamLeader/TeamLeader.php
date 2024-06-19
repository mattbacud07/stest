<?php

namespace App\Http\Controllers\TeamLeader;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Traits\GlobalVariables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamLeader extends Controller
{
    use GlobalVariables;
    public function get_request_to_assign(){
        try {
            $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approver_level', 'a.approver_name')
            ->where('equipment_handling.status', self::INSTALLATION_TL)
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
            ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
            ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
            ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
            ->get();
            return response()->json([ 'request_to_assign' => $data ]);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage() ]);
        }
    }

}
