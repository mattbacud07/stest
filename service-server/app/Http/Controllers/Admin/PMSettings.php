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
