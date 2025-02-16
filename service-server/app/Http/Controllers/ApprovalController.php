<?php

namespace App\Http\Controllers;

use App\Models\Approvals;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $service_id = $request->service_id;
        $type = $request->type;
        try {
            $q = Approvals::where([
                'service_id' => $service_id,
                'type' => $type,
            ])
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u','approvals.user_id','=','u.id')
            ->select('approvals.*', DB::raw('CONCAT(u.first_name, " ", u.last_name) as approver'))
            ->orderBy('level', 'asc')
            ->get();
            if (!$q) throw new Exception('Unable get the data');

            return response()->json([
                'approvals' => $q,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
