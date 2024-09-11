<?php

namespace App\Http\Controllers\Corrective;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Models\Roles;
use App\Models\RoleUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CM extends Controller
{
    /** Get Preventive Mantenance Schedule */
    public function get_preventive_maintenance(Request $request, Guard $guard)
    {
        /** Search Column */
        $columnMappings = [
            'item_code' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'item_code'],
            'description' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'description'],
            'serial_number' => ['table' => 'equipment_peripherals', 'db' => 'mysql', 'column' => 'serial_number'],
            'institution_name' => ['table' => 'i', 'db' => 'mysqlSecond', 'column' => 'name'],
            'institution_address' => ['table' => 'i', 'db' => 'mysqlSecond', 'column' => 'address'],
            'scheduled_at' => ['table' => 'pm', 'db' => 'mysql', 'column' => 'scheduled_at'],
            'schedule' => ['table' => 'ps', 'db' => 'mysql', 'column' => 'schedule'],
            'ssu' => ['table' => 'eh', 'db' => 'mysql', 'column' => 'ssu'],
            'engineer' => ['table' => 'pm', 'db' => 'mysql', 'column' => 'engineer'],
            'status_after_service' => ['table' => 'pm', 'db' => 'mysql', 'column' => 'status_after_service'],
            'status' => ['table' => 'pm', 'db' => 'mysql', 'column' => 'status']
        ];
        
        

        /**Server Mode Details */
        $current_page = $request->current_page ?? 0;
        $pageSize = $request->pagesize ?? 0;
        $searchColumn = $request->searchColumn ?? [];
        $search = $request->search ?? '';


        $userArea = $guard->user()->area;
        $user_id = $guard->user()->id;
        $userRoleID = [2, 3];  //2 = 'Team Leader' 3 = 'Engineer'
        $getUserSSU = RoleUser::where('user_id', $user_id)
            ->whereIn('role_id', $userRoleID)
            ->first();
        $area = 0;
        try {
            //initialization
            // $query = PM::query();

            $query = DB::table('preventive_maintenance  as pm')->where('pm.work_type','PM')
            // Select the required columns
            ->select(
                'pm.*',
                'm.item_code',
                'm.description',
                'equipment_peripherals.serial_number',
                'ps.schedule',
                'user.first_name',
                'user.last_name',
                'eh.id as eh_id',
                'i.name as institution_name',
                'i.address as institution_address',
                'eh.ssu'
            )
            // Join with equipment (master_data)
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'pm.item_id', '=', 'm.id')
            
            // Join with equipment_peripherals
            ->leftJoin('equipment_peripherals', 'pm.equipment_peripheral_id', '=', 'equipment_peripherals.id')
            
            // Join with frequency (PM_Setting)
            ->leftJoin('pm_setting as ps', 'pm.item_id', '=', 'ps.equipment')
            
            // Join with users (engineer)
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as user', 'pm.engineer', '=', 'user.id')
            
            // Join with eh (EhServicesModel) and mt_bp_institutions
            ->leftJoin('equipment_handling as eh', 'pm.service_id', '=', 'eh.id')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'eh.institution', '=', 'i.id');
            
            

                

            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }


            /** Flexible Condition for Specific request */
            if ($request->has('category')) {
                if ($request->category === Roles::TLRole) {
                    $today = Carbon::today();
                    $lastDayOfNextMonth = $today->addMonth()->endOfMonth();

                        $query->where('eh.ssu', $getUserSSU['SSU']);
                    // $query->where('pm.scheduled_at', '<=', $lastDayOfNextMonth);
                    $query->orWhere('pm.status', PM::NotSet);
                } else {
                    $arrayStatus = [PM::Delegated, PM::Accepted, PM::InTransit, PM::InProgress, PM::Completed];
                        $query->where('eh.ssu', $getUserSSU['SSU'])
                        ->where(['pm.engineer' => $user_id,])
                        ->whereIn(
                            'pm.status',
                            $arrayStatus
                        );
                }
            }

            // $get_pm_data = $query->get();
            $get_pm_data = $query->paginate(
                $pageSize,
                ['*'],
                'page',
                $current_page
            );

            if (!$get_pm_data) {
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'pm_data' => $get_pm_data,
                'area' => $area,
                // 'ssUS' => $getUserSSU,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
