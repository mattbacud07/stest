<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SSU;
use Illuminate\Http\Request;

class SSUController extends Controller
{
    public function index()
    {
        $ssus = SSU::all(); // Fetch all SSUs
        return response()->json([
            'ssu' => $ssus,
        ]);
    }


    // Store a newly created SSU
    public function store(Request $request)
    {
        $checkExist = SSU::where('ssu', $request->ssu)->exists();
        if (!$checkExist) {
            SSU::create([
                'ssu' => $request->ssu,
            ]);

            return response()->json([
                'success' => true
            ], 201);
        } else {
            return response()->json([
                'exist' => true
            ]);
        }
    }


    // Update the specified SSU
    public function edit(Request $request)
    {
        $checkExist = SSU::where('ssu', $request->ssu)->where('id','!=',$request->id)->exists();
        if (!$checkExist) {
            $ssu = SSU::findOrFail($request->id); // Find the SSU by ID
            $ssu->update([
                'ssu' => $request->ssu, // Update the SSU field
            ]);

            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'exist' => true
            ]);
        }
    }



    // Remove the specified SSU
    public function delete(Request $request)
    {
        $ssu = SSU::findOrFail($request->id); // Find the SSU by ID
        $ssu->delete(); // Delete the SSU

        return response()->json([
            'success' => true
        ]);
    }
}
