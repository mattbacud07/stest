<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem as ModelsChecklistItem;
use App\Models\ChecklistItemAcquired;
use Illuminate\Http\Request;

class ChecklistItem extends Controller
{
    public function getChecklistItem(){
        $items = ModelsChecklistItem::get();

        return response()->json([
            'items' => $items,
        ]);
    }
    
    public function getChecklistItemAcquired(Request $request){
        $items = ChecklistItemAcquired::where('service_id', $request->service_id)->get();

        return response()->json([
            'items' => $items,
        ]);
    }
}
