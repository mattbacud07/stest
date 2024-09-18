<?php

namespace App\Http\Controllers\EquipmentHandling;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EquipmentHandlingService\EHBasicOperation;
use App\Services\EquipmentHandlingService\EHTaskOperation;
use Illuminate\Contracts\Auth\Guard;

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
    public function showBasedOnCondition(Request $request, Guard $guard)
    {
        $user_id = $guard->user()->id;
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

}
