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
        $query = MasterData::select('master_data.id','master_data.item_category','master_data.item_code','master_data.description','master_data.status as item_status','category.name')
        ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName().'.mt_item_categories as category', 'category.id','=','master_data.item_category')
        ->where('master_data.status', 1);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $searchColumn) {
                foreach ($searchColumn as $column) {
                    if($column === 'name'){
                        $q->orWhere('category.'.$column, 'like', "%{$search}%");
                    }
                    else $q->orWhere('master_data.'.$column, 'like', "%{$search}%");
                }
            });
        }
        if($request->has('item_category') && $request->filled('item_category')){
            $item_category = $request->item_category;
            $query->whereIn('master_data.item_category', $item_category);
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
