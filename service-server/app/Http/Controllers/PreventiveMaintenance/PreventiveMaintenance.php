<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\PreventiveMaintenance\PMOptionsAction;
use App\Models\PreventiveMaintenance\PMPartsUsed;
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
use App\Services\PM\GeneratePMSched;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\map;

class PreventiveMaintenance extends Controller
{
    use Maintenance, GlobalVariables;

    protected $generatePMSched;

    public function __construct(GeneratePMSched $generatePMSched)
    {
        $this->generatePMSched = $generatePMSched;
    }

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
        $sortColumn = $request->sort_column ?? '';
        $sortDirection = $request->sort_direction ?? '';
        $work_type = $request->work_type ?? '';


        $userArea = $guard->user()->area;
        $user_id = $guard->user()->id;
        $userRoleID = [2, 3];  //2 = 'Team Leader' 3 = 'Engineer'
        $getUserSSU = RoleUser::where('user_id', $user_id)
            ->whereIn('role_id', $userRoleID)
            ->get();
        $area = 0;
        try {
            //initialization
            // $query = PM::query();

            $query = DB::table('preventive_maintenance  as pm')->where('pm.work_type', $work_type)
                // Select the required columns
                ->select(
                    'pm.*',
                    'm.item_code',
                    'm.description',
                    'equipment_peripherals.serial_number',
                    'ps.schedule',
                    // 'user.first_name',
                    // 'user.last_name',
                    DB::raw('CONCAT(user.first_name, " ", user.last_name) as username'),
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
                $query->where(function ($q) use ($search, $columnMappings) {
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
                    $getTLSSU = $getUserSSU->filter(function ($val) {
                        return $val['role_id'] === 2;
                    })->map(function ($val) {
                        return $val['SSU'];
                    });


                    $query->where('eh.ssu', $getTLSSU);
                    if ($request->filled('filterOptions')) {
                        if (in_array('assign-next-month', $request->filterOptions)) {
                            $query->where('pm.scheduled_at', '<=', $lastDayOfNextMonth);
                            $query->where('pm.status', PM::Scheduled);
                        } else {
                            $query->where('pm.status', PM::NotSet);
                        }
                    }
                } else {
                    $getEngineerSSU = $getUserSSU->filter(function ($val) {
                        return $val['role_id'] === 3;
                    })->map(function ($val) {
                        return $val['SSU'];
                    });
                    $arrayStatus = [PM::Delegated, PM::Accepted, PM::InTransit, PM::InProgress, PM::Completed];
                    $query->where('eh.ssu', $getEngineerSSU)
                        ->where(['pm.engineer' => $user_id,])
                        ->whereIn(
                            'pm.status',
                            $arrayStatus
                        );
                }
            }


            /**Filtering */
            if ($request->filled('filterStatus')) {
                $query->whereIn('pm.status', $request->filterStatus);
            }
            if ($request->filled('filterInstitution')) {
                $filterInstitution = array_map(function ($data) {
                    return $data['iId'];
                }, $request->filterInstitution);
                $query->whereIn('i.id', $filterInstitution);
            }
            if ($request->filled('filterSchedule_at')) {
                $startDate = Carbon::parse($request->filterSchedule_at[0])->startOfDay();
                $endDate = Carbon::parse($request->filterSchedule_at[1])->endOfDay();
                $query->whereBetween('pm.scheduled_at', [$startDate, $endDate]);
            }
            if ($request->filled('filterStatusAfterService')) {
                $query->whereIn('pm.status_after_service', $request->filterStatusAfterService);
            }
            if ($request->filled('filterTag')) {
                $query->whereIn('pm.tag', $request->filterTag);
            }


            // $get_pm_data = $query->get();
            $get_pm_data = $query->orderBy($sortColumn, $sortDirection)->paginate(
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
                ->with(['eh' => function ($q) {
                    $q->select('i.id', 'equipment_handling.id', 'equipment_handling.institution', 'i.name', 'i.address', 'equipment_handling.ssu')
                        ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id');
                }])
                ->with(['actions' => function ($q) {
                    $q->select('id', 'pm_id', 'action', 'work_type');
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
        $serial = $request->serial ?? 0;

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
                    //Note ===================== -> Need to add here the serial number for updating -> very important thing ======================
                    'serial' => $serial,
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
        $spareParts = $request->spareParts ?? [];
        $status_after_service = $request->status_after_service ?? '';
        $remarks = $request->remarks ?? '';
        $work_type = $request->work_type ?? '';

        $pm_data = PM::find($id); //Get pm data for updating specific record
        //Get pm_data for generating next pm sched
        $get_pm_data = PM::select('preventive_maintenance.*', 'pm_setting.schedule')
        ->leftJoin('pm_setting', 'preventive_maintenance.item_id', '=', 'pm_setting.equipment')
        ->where('preventive_maintenance.id', $id)
        ->first();


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
                $monitoring_end = null;
                if($status_after_service  === PM::further_monitoring) $tag = PM::pm_tag_set_observation;
                else if($status_after_service  === PM::non_operational) {
                    $tag = PM::pm_tag_backlog;
                    /** Create CM Schedule [if Status After Service is Non Operational] */
                    PM::create([
                        'equipment_peripheral_id' => $get_pm_data['equipment_peripheral_id'],
                        'service_id' => $get_pm_data['service_id'],
                        'item_id' => $get_pm_data['item_id'],
                        'date_installed' => $get_pm_data['date_installed'],
                        'status' => PM::Scheduled,
                        'work_type' => 'CM',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else {
                    $daysToAdd = $work_type === 'PM' ? 30 : 10;
                    $monitoring_end = Carbon::now()->startOfDay()->addDays($daysToAdd);
                    $tag = PM::pm_tag_under_observation;
                }

                /** Main Data to update */
                $data = [
                    'end_date' => Carbon::now(),
                    'status_after_service' => $status_after_service,
                    'tag' => $tag,
                    'remarks' => $remarks,
                    'status' => PM::Completed,
                    'monitoring_end' => $monitoring_end
                ];

                //Actions Done
                foreach ($actions as $action) {
                    WorksDone::insert([
                        'pm_id' => $id,
                        'action' => $action,
                        'work_type' => $work_type,
                    ]);
                }


                //Spareparts
                foreach ($spareParts as $sparePart) {
                    PMPartsUsed::insert([
                        'pm_id' => $id,
                        'item_id' => $sparePart['item_id'],
                        'qty' => $sparePart['qty'],
                        'dr' => $sparePart['dr'],
                        'si' => $sparePart['si'],
                        'remarks' => $sparePart['remarks'],
                        'created_at' => Carbon::now(),
                    ]);
                }


                /** Generate another Schedule after this submitted */
                $dateInstalled = Carbon::parse($get_pm_data['scheduled_at']);
                $expiration_date = Carbon::now()->endOfYear();

                $scheduled_at = $this->generatePMSched->calculateSchedFrequency($dateInstalled, $get_pm_data['schedule']);
                $allSched = $this->generatePMSched->calculateAllSchedFrequency($dateInstalled, $get_pm_data['schedule'], $expiration_date);

                /** Create PM Schedule for next Schedule */
                if($work_type === 'PM'){
                    PM::create([
                        'equipment_peripheral_id' => $get_pm_data['equipment_peripheral_id'],
                        'service_id' => $get_pm_data['service_id'],
                        'list_scheduled' => $allSched,
                        'scheduled_at' => $scheduled_at,
                        'item_id' => $get_pm_data['item_id'],
                        'date_installed' => $get_pm_data['date_installed'],
                        'status' => PM::Scheduled,
                        'work_type' => 'PM',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            } // END OF IN PROGRESS

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
