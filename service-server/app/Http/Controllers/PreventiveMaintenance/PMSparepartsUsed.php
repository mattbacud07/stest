<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance\PMPartsUsed;
use Illuminate\Http\Request;

class PMSparepartsUsed extends Controller
{
    public function view(Request $request){

        try {
            $spareparts_used = PMPartsUsed::where('pm_id', $request->pm_id)
            ->with(['equipment' => function($q) {
                $q->select('id','item_code', 'description');
            }])
            ->get();

            return response()->json([
                'spareparts' => $spareparts_used,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }

    }
}
