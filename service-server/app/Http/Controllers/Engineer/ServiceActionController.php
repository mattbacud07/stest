<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Traits\GlobalVariables;

class ServiceActionController extends Controller
{
    use GlobalVariables;
    /**
     * Get Engineers Data
     */
    public function get_engineers_data()
    {
        $role_id = Roles::engineerRoleID;
        $engineers = RoleUser::with(['users' => function ($q) {
            $q->select('id', 'first_name', 'last_name');
        }])
            ->whereHas('roles', function ($q) use ($role_id) {
                $q->where('role_id', $role_id);
            })
            ->get();

        return response()->json([
            'engineers' => $engineers,
        ], 200);
    }
}
