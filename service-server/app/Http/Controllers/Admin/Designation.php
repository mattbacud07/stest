<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation as ModelDesignation;
use Illuminate\Http\Request;

class Designation extends Controller
{
    /** Get all designations */
    public function get_designations()
    {
        try {
            $designations = ModelDesignation::get();

            return response()->json([
                'designations' => $designations
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }
}
