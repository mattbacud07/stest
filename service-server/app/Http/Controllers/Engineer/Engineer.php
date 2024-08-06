<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Services\Engineer\PMTask;
use App\Traits\GlobalVariables;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Engineer extends Controller
{
    use GlobalVariables;

    protected $pmTask;
    public function __construct(PMTask $pmTask)
    {
        $this->pmTask = $pmTask;
    }

    public function get_assigned_request(Request $request){
        $user_id = $request->id;
        try {
            $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'e.name as request_name','iR.name as internal_name','a.approver_level', 'a.approver_name')
            ->where(['equipment_handling.status' => self::INSTALLATION_ENGINEER, 'equipment_handling.installer' => $user_id])
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
            ->leftjoin('internal_external_requests as e', 'equipment_handling.external_request', '=', 'e.id')
            ->leftjoin('internal_external_requests as iR', 'equipment_handling.internal_request', '=', 'iR.id')
            ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
            ->get();
            return response()->json([ 'assigned_data' => $data ]);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage() ]);
        }
    }

    public function accept_pm_task(Request $request, Guard $guard){

        $id = $request->id;
        $user_id = $guard->user()->id;
        DB::beginTransaction();
        try {
            $this->pmTask->accept_pm_task($id, $user_id);

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }
}
