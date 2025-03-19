<?php

namespace App\Services;

use App\Models\PreventiveMaintenance\PMPartsUsed;
use App\Models\WorksDone;
use Exception;

class ActionsDoneService
{
    public function declare_actions_done($data)
    {
        try {
            foreach ($data as $record) {
                WorksDone::create($record); // Uses Eloquent and triggers events
            }
            return true;
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function declare_spareparts_used($spareparts){
        try {
            foreach ($spareparts as $parts) {
                PMPartsUsed::create($parts); // Uses Eloquent and triggers events
            }
            return true;
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
