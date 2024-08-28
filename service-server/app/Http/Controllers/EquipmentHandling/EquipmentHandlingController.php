<?php

namespace App\Http\Controllers\EquipmentHandling;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EquipmentHandlingService\EHBasicOperation;
use App\Services\EquipmentHandlingService\EHTaskOperation;

class EquipmentHandlingController extends Controller
{
    protected $EHBasicOperation;
    protected $EHTaskOperation;
    public function __construct(EHBasicOperation $EHBasicOperation, EHTaskOperation $EHTaskOperation)
    {
        $this->EHBasicOperation = $EHBasicOperation;
        $this->EHTaskOperation = $EHTaskOperation;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->EHBasicOperation->getAllEquipmentHandlings();

        return response()->json(['equipment_handling' => $data], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function getEquipmentHandlingById(Request $request)
    {
        $service_id = $request->service_id ?? 0;

        $data = $this->EHBasicOperation->getEquipmentHandlingByServiceId($service_id);

        return response()->json([
            'equipment_handling' => $data,
        ], 200);
    }


    /**
     * Display the specified resource base on condition.
     */
    public function showBasedOnCondition(Request $request)
    {
        $user_id = $request->user_id;
        $category = $request->category ?? '';
        $result = $this->EHTaskOperation->getEquipmentHandlingByUserId($user_id, $category);

        // return response()->json(['equipment_handling' => $data], 200);

        // Access data and count
        $data = $result['data'];
        $count = $result['count'];

        // Return JSON response
        return response()->json([
            'equipment_handling' => $data,
            'count' => $count
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
