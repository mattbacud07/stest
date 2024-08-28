<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\PreventiveMaintenance\PMOptionsAction;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Models\WorksDone;
use App\Services\ActionsDoneService;
use App\Traits\GlobalVariables;
use App\Traits\Maintenance;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenance extends Controller
{
    use Maintenance, GlobalVariables;

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

            $query = DB::table('preventive_maintenance  as pm')
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
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'eh.institution', '=', 'i.id')
            
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
            );

                

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
                    $query->where('pm.scheduled_at', '<=', $lastDayOfNextMonth);
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


    /** Get PM - Record */
    public function get_pm_record_details(Request $request)
    {
        $id = $request->id;

        try {
            $get_record = PM::where('id', $id)
                ->with(['equipment' => function ($q) {
                    $q->select('id', 'item_code', 'description');
                }])
                ->with(['equipment_peripherals' => function ($q) {
                    $q->select('id', 'serial_number');
                }])
                ->with(['user' => function ($q) {
                    $q->select('id', 'first_name', 'last_name');
                }])
                ->with(['actions' => function ($q) {
                    $q->select('id', 'pm_id', 'action', 'work_type');
                }])
                ->with(['eh' => function ($q) {
                    $q->select('i.id', 'equipment_handling.id', 'equipment_handling.institution', 'i.name', 'i.address', 'equipment_handling.ssu')
                        ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id');
                }])
                ->get();

            if (!$get_record) {
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'details' => $get_record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }





    /** PM Proccessing */
    public function pm_process(Request $request)
    {
        $id = $request->id ?? 0;
        $engineer = $request->engineer ?? 0;

        $pm_data = PM::find($id);

        if (!$pm_data) {
            return response()->json([
                'error' => 'PM record not found',
            ], 404);
        }

        $get_latest_status = $pm_data->status;

        try {
            DB::beginTransaction();
            $data = [];
            if ($get_latest_status === PM::Scheduled) {
                $data = [
                    'delegation_date' => Carbon::now(),
                    'engineer' => $engineer,
                    'status' => PM::Delegated,
                ];
            } elseif ($get_latest_status === PM::Delegated) {
                return response()->json(['delegated_exist' => true,], 200);
            }

            $updatePM = PM::where('id', $id)->update($data);

            if (!$updatePM) {
                throw new Exception('Error updating PM record');
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'errorE' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }




    /** PM Accepted */
    public function pm_accepted(Request $request)
    {
        $id = $request->id ?? 0;

        $pm_data = PM::find($id);

        if (!$pm_data) {
            return response()->json([
                'error' => 'PM record not found',
            ], 404);
        }

        $get_latest_status = $pm_data->status;

        try {
            DB::beginTransaction();
            $data = [];
            if ($get_latest_status === PM::Delegated) {
                $data = [
                    'date_accepted' => Carbon::now(),
                    'status' => PM::Accepted,
                ];
            }

            $updatePM = PM::where('id', $id)->update($data);

            if (!$updatePM) {
                throw new Exception('Error updating PM record');
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }





    /** PM Task Processing */
    public function pm_task_processing(Request $request, ActionsDoneService $action_done)
    {
        $id = $request->id ?? 0;
        $actions = $request->actions ?? [];
        $status_after_service = $request->status_after_service ?? '';
        $remarks = $request->remarks ?? '';

        $pm_data = PM::find($id);

        if (!$pm_data) {
            return response()->json([
                'error' => 'PM record not found',
            ], 404);
        }

        $get_latest_status = $pm_data->status;

        try {
            DB::beginTransaction();
            $data = [];
            if ($get_latest_status === PM::Accepted) {
                $data = [
                    'departed_date' => Carbon::now(),
                    'status' => PM::InTransit,
                ];
            } else if ($get_latest_status === PM::InTransit) {
                $departedDate = Carbon::parse($pm_data->departed_date);
                $startDate = Carbon::now();

                $daysDifference = $departedDate->diffInDays($startDate);
                $hoursDifference = $departedDate->diffInHours($startDate) % 24; // Remaining hours after full days
                $minutesDifference = $departedDate->diffInMinutes($startDate) % 60;

                $totalDuration = $daysDifference . ' days, ' . $hoursDifference . ' hours, ' . $minutesDifference . ' minutes';

                $data = [
                    'start_date' => Carbon::now(),
                    'travel_duration' => $totalDuration,
                    'status' => PM::InProgress,
                ];
            } else if ($get_latest_status === PM::InProgress) {

                $data = [
                    'end_date' => Carbon::now(),
                    'status_after_service' => $status_after_service,
                    'remarks' => $remarks,
                    'status' => PM::Completed,
                ];

                foreach ($actions as $action) {
                    WorksDone::insert([
                        'pm_id' => $id,
                        'action' => $action,
                        'work_type' => 'PM',
                    ]);
                }
            }

            $updatePM = PM::where('id', $id)->update($data);

            if (!$updatePM) {
                throw new Exception('Error updating PM record');
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }



    /** get Standard Actions */
    public function getStandardActions()
    {
        try {
            DB::beginTransaction();

            $actions = PMOptionsAction::get();

            DB::commit();
            return response()->json(['actions' => $actions,]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage(),]);
        }
    }
}
