<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\MasterData;
use App\Models\MasterDataInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EhMainController extends Controller
{
    // public function index(Request $request)
    // {
    //     $data = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'iE.name as request_name','a.approver_level', 'a.approver_name')
    //     ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
    //     ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
    //     ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
    //     ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level')
    //     ->get();

    //     return response()->json([
    //         'equipment_handling' => $data,
    //     ], 200);
    // }

    // /** Get Data Based on Specific User */
    // public function show(Request $request){
    //     $query = EhServicesModel::select('equipment_handling.*', 'i.name', 'i.address','i.area', 'u.first_name', 'u.last_name', 'iE.name as request_name','a.approver_level', 'a.approver_name')
    //     ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'equipment_handling.requested_by', '=', 'u.id')
    //     ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
    //     ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
    //     ->leftjoin('approver_designation as a', 'equipment_handling.status', '=', 'a.approver_level');

    //     if($request->has('user_id')){
    //         $query->where(['requested_by' => $request->input('user_id')]);
    //     }

    //     $data = $query->get();

    //     return response()->json([
    //         'equipment_handling' => $data,
    //     ], 200);
    // }


    /** Get Data Based on Specific Equipment Handling ID */

    /**
     * Master Data of Equipments.
     */
    public function master_data_equipments(Request $request)
    {
        $current_page = $request->current_page ?? 0;
        $pageSize = $request->pagesize ?? 0;
        $search = $request->search ?? '';
        $searchColumn = $request->searchColumn ?? [];

        // $category = [3,4,1]; ->whereIn('item_category', $category)
        $query = MasterData::where('status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $searchColumn) {
                foreach ($searchColumn as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }
        $equipments = $query->paginate(
            $pageSize,
            ['*'],
            'page',
            $current_page
        );

        // $institutions = DB::connection('mysqlSecond')->table('mt_bp_institutions')->get();

        return response()->json([
            'equipments' => $equipments,
            // 'institutions' => $institutions,
            'sarrcg' => $search,
        ]);
    }





    /** Master Data Institution */
    public function get_institution(){
        $institutions = MasterDataInstitution::get();
        return response()->json([
            'institutions' => $institutions,
        ]);
    }
}
