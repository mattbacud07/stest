<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SSU;
use Illuminate\Http\Request;

class SSUController extends Controller
{
    public function index()
    {
        $items = SSU::all(); // Fetch all SSUs
        return response()->json([
            'item' => $items,
        ]);
    }


    // Store a newly created SSU
    public function store(Request $request)
    {
        $checkExist = SSU::where('item', $request->item)->exists();
        if (!$checkExist) {
            SSU::create([
                'item' => $request->item,
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
        $checkExist = SSU::where('item', $request->item)->where('id','!=',$request->id)->exists();
        if (!$checkExist) {
            $item = SSU::findOrFail($request->id); // Find the SSU by ID
            $item->update([
                'item' => $request->item, // Update the SSU field
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
