<?php

namespace App\Http\Controllers\EquipmentHandling;

use App\Http\Controllers\Controller;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use App\Services\EquipmentHandlingService\EHBasicOperation;
use App\Services\EquipmentHandlingService\EHTaskOperation;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        $roleUsers = RoleUser::leftjoin('roles', 'roles.roleID', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('role_user.*', 'roles.role_name', 'roles.roleID', 'roles.permissions') // Select the fields you need
            ->get();

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
        $user_id = Auth::user()->id;
        $category = $request->category ?? '';
        $myRequest = $request->myRequest ?? false;
        $result = $this->EHTaskOperation->getEquipmentHandlingByUserId($user_id, $category, $myRequest);

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
