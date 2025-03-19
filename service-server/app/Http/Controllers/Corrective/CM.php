<?php

namespace App\Http\Controllers\Corrective;

use App\Http\Controllers\Controller;
use App\Models\CorrectiveMaintenance\CorrectiveMaintenance;
use App\Models\CustomerDetails;
use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Services\ActionsDoneService;
use App\Services\TaskDelegationService;
use App\Services\Validation\UserRolesValidator;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CM extends Controller
{
    use GlobalVariables;

    protected $role_validator;
    protected $task_delegation;
    protected $action;
    public function __construct(
        UserRolesValidator $role_validator,
        TaskDelegationService $task_delegation,
        ActionsDoneService $actions
    ) {
        $this->role_validator = $role_validator;
        $this->task_delegation = $task_delegation;
        $this->action = $actions;
    }


    public function get_all(Request $request)
    {
        $user_id = Auth::user()->id;
        $tl_assitant_sbu = RoleUser::where('user_id', $user_id)->whereIn('role_id', [Roles::TLRoleID, Roles::SBUAssistantRoleID])->value('sbu');
        $current_role = (int) $request->current_role ?? 0;

        /** Search Column */
        $columnMappings = [
            'id' => ['table' => 'corrective_maintenance', 'db' => 'mysql', 'column' => 'id'],
            'item_code' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'item_code'],
            'description' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'description'],

            'serial' => ['table' => 'smd', 'db' => 'mysql', 'column' => 'serial'],

            'institution_name' => ['table' => 'i', 'db' => 'mysqlSecond', 'column' => 'name'],
            'address' => ['table' => 'i', 'db' => 'mysqlSecond', 'column' => 'address'],

            'first_name' => ['table' => 'u_by', 'db' => 'mysqlSecond', 'column' => 'first_name'],
            'last_name' => ['table' => 'u_by', 'db' => 'mysqlSecond', 'column' => 'last_name'],
            'first_names' => ['table' => 'u_to', 'db' => 'mysqlSecond', 'column' => 'first_name'],
            'last_names' => ['table' => 'u_to', 'db' => 'mysqlSecond', 'column' => 'last_name'],
            // 'status_after_service' => ['table' => 'pm', 'db' => 'mysql', 'column' => 'status_after_service'],
            'status' => ['table' => 'corrective_maintenance', 'db' => 'mysql', 'column' => 'status']
        ];



        /**Server Mode Details */
        $current_page = $request->current_page ?? 0;
        $pagesize = $request->pagesize ?? 0;
        $search = $request->search ?? '';
        $sortColumn = $request->sort_column ?? '';
        $sortDirection = $request->sort_direction ?? '';


        try {
            //initialization
            // $query = PM::query();

            $query = CorrectiveMaintenance::select(
                'corrective_maintenance.*',
                'm.item_code',
                'm.description',
                'smd.id as smd_id',
                'smd.master_data_id',
                'smd.equipment',
                'smd.serial',
                'smd.frequency',
                'smd.sbu',
                DB::raw('CONCAT(u_by.first_name, " ", u_by.last_name) as delegated_by'),
                DB::raw('CONCAT(u_to.first_name, " ", u_to.last_name) as delegated_to'),
                'i.name as institution_name',
                'i.address',
            )

                ->leftJoin('service_master_data as smd', 'corrective_maintenance.item_id', '=', 'smd.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'smd.master_data_id', '=', 'm.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'corrective_maintenance.institution', '=', 'i.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u_by', 'corrective_maintenance.delegated_by', '=', 'u_by.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u_to', 'corrective_maintenance.delegated_to', '=', 'u_to.id');


            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }


            if ($this->role_validator->userHasRole($current_role)) {
                $query->where(function ($subQuery) use ($current_role, $user_id, $tl_assitant_sbu) {
                    if ($current_role === Roles::TLRoleID || $current_role === Roles::SBUAssistantRoleID) {
                        $subQuery->where(function ($q) use ($tl_assitant_sbu) {
                            $q->whereExists(function ($existQuery) use ($tl_assitant_sbu) {
                                $existQuery->select(DB::raw(1))
                                    ->from('service_master_data as smd')
                                    ->where('smd.sbu', '=', $tl_assitant_sbu)
                                    ->whereColumn('smd.id', '=', 'corrective_maintenance.item_id');
                            });
                        });
                    } elseif ($current_role === Roles::engineerRoleID) {
                        $subQuery->where('corrective_maintenance.delegated_to', '=', $user_id);
                    }
                });

                /** Filtering */
                if ($request->has('filterStatus')) {
                    $status = $request->filterStatus;
                    $query->whereIn('corrective_maintenance.status', $status);
                }
                if ($request->has('filterInstitution')) {
                    $institution_ids = $request->filterInstitution;
                    $institutions = array_map(function ($institution_ids) {
                        return $institution_ids['institution_id'];
                    }, $institution_ids ?? []);
                    $query->whereIn('corrective_maintenance.institution', $institutions);
                }


                $get_pm_data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                    $pagesize,
                    ['*'],
                    'page',
                    $current_page
                );
            } else return response()->json(['error' => 'Forbidden'], 403);

            return response()->json([
                'cm_data' => $get_pm_data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }




    /** Get By Row ID */
    public function get_cm_record_details(Request $request)
    {
        $id = $request->id;
        $module_type = $request->module_type;

        try {
            $data = CorrectiveMaintenance::with(['equipment'])
                ->with(['institution'])
                ->with([
                    'task_delegation' => function ($q) use ($module_type) {
                        $q->with([
                            'task_activity' => function ($q) use ($module_type) {
                                $q->where('type', $module_type);
                            },
                            'actions_taken' => function ($q) use ($module_type) {
                                $q->where('work_type', $module_type);
                            },
                            'spareparts' => function ($q) use ($module_type) {
                                $q->where('type', $module_type)
                                    ->with('equipment'); // Nested eager loading
                            },
                            'customer' => function ($q) use ($module_type) {
                                $q->where('type', $module_type);
                            }
                        ]);
                    }
                ])



                ->with(['latest_task_delegation:id,service_id,status'])  // Get all task delegations

                ->with(['task_delegation_all' => function ($q) use ($module_type) {
                    $q->select(
                        'engineer_task_delegation.*',
                        DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    )->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'engineer_task_delegation.assigned_to', '=', 'assigned_to.id');
                    $q->with([
                        'items_acquired' => function ($q) use ($module_type) {
                            $q->where('work_type', $module_type);
                        },
                        'task_activity' => function ($q) use ($module_type) {
                            $q->where('type', $module_type);
                        },
                        'actions_taken' => function ($q) use ($module_type) {
                            $q->where('work_type', $module_type);
                        },
                        'spareparts' => function ($q) use ($module_type) {
                            $q->where('type', $module_type);
                        }
                    ]);
                }])  // Get all task delegations
                ->find($id);

            if (!$data) {
                throw new Exception('Error in retrieving the data');
            }
            return response()->json([
                'cm' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function checkIfRequestExist($service_id)
    {
        $requestExist = EngineerTaskDelegation::where([
            'service_id' => $service_id,
            'type' => self::CM,
            'active' => 1,
        ])->first();

        return response()->json(['exist' => (bool) $requestExist]);
    }


    /** PM Delegation */
    public function pm_delegate_engineer(Request $request)
    {
        $id = $request->id ?? 0;
        $user_id = Auth::user()->id;
        $engineer = $request->engineer ?? 0;


        try {
            DB::beginTransaction();
            $pm_data = CorrectiveMaintenance::find($id);

            if (!$pm_data) {
                return response()->json([
                    'error' => 'CM record not found',
                ], 404);
            }

            $this->checkIfRequestExist($id);

            $updatePM = $pm_data->update([
                'status' => PM::Delegated,
                'delegated_by' => $user_id,
                'delegated_to' => $engineer,
            ]);

            if (!$updatePM || !$pm_data->wasChanged()) {
                throw new Exception('Error updating PM record');
            }

            $this->task_delegation->task_delegation_log(
                $id,
                $engineer,
                $user_id,
                PM::Delegated,
                self::CM
            );

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'errorE' => $e->getMessage(),
            ], 500);
        }
    }





    /** Accept decline request */
    public function accept_decline(Request $request)
    {
        $user_id = Auth::user()->id;
        $service_id = $request->service_id;
        $delegation_id = $request->delegation_id;
        $status = $request->status; // Accept or Decline
        $remarks = $request->remarks;
        try {
            DB::beginTransaction();

            $this->checkIfRequestExist($service_id);

            $updateInPM = [];
            if ($status == 'accepted') {
                $update_delegation_status = $this->task_delegation->update_task_delegation_log(
                    $service_id,
                    self::DELEGATED,
                    self::CM,
                    self::ACCEPTED,
                    $remarks
                );

                if (!$update_delegation_status) throw new Exception('Failed to accept delegation status');

                /** Create Task Log for Task Delegation */
                $this->task_delegation->task_activities(
                    $delegation_id,
                    EngineerActivities::Accepted,
                    Carbon::now(),
                    self::CM
                );

                $updateInPM = [
                    'status' => PM::Accepted
                ];
            } else {
                $update_delegation_status = $this->task_delegation->update_task_delegation_log(
                    $service_id,
                    self::DELEGATED,
                    self::CM,
                    self::DECLINED,
                    $remarks,
                    0 // set active status to 0
                );

                if (!$update_delegation_status) throw new Exception('Failed to update delegation status. Accepted already.');

                $updateInPM = [
                    'status' => EngineerTaskDelegation::DECLINED,
                    'delegated_by' => null,
                    'delegated_to' => null,
                ]; 
            }

            /** Update PM status */
            $pm = CorrectiveMaintenance::find($service_id);
            if (!$pm) {
                throw new Exception('PM record not found');
            }
            $updateStatus = $pm->update($updateInPM);
            if (!$updateStatus || !$pm->wasChanged('status')) {
                throw new Exception('Failed to update PM status');
            }

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }





    /** In Transit */
    public function in_transit(Request $request)
    {
        $user_id = Auth::user()->id;
        $delegation_id = $request->delegation_id;
        $id = $request->id;
        try {
            DB::beginTransaction();
            /** Create Task Log for Task Delegation */
            $this->task_delegation->task_activities(
                $delegation_id,
                EngineerActivities::InTransit,
                Carbon::now(),
                self::CM
            );


            /** Update Dlegation */
            $updated = EngineerTaskDelegation::find($delegation_id)?->update([
                'status' => EngineerActivities::InTransit
            ]);

            if (!$updated) {
                throw new Exception('Failed to update Engineer Task Delegation status');
            }


            /** Update PM status */
            $pm = CorrectiveMaintenance::find($id);
            if (!$pm) {
                throw new Exception('CM record not found');
            }
            $updateStatus = $pm->update([
                'status' => EngineerActivities::InTransit
            ]);
            if (!$updateStatus || !$pm->wasChanged('status')) {
                throw new Exception('Failed to update PM status');
            }

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }


    /** Start Task */
    public function start_task(Request $request)
    {
        $user_id = Auth::user()->id;
        $delegation_id = $request->delegation_id;
        $in_transit = Carbon::parse($request->in_transit);
        $id = $request->id;
        $now = Carbon::now();
        $hours = $in_transit->diffInHours($now);
        $mins = $in_transit->diffInMinutes($now);
        try {
            DB::beginTransaction();
            /** Create Task Log for Task Delegation */
            $this->task_delegation->task_activities(
                $delegation_id,
                EngineerActivities::Started,
                Carbon::now(),
                self::CM
            );


            /** Update Dlegation */
            $updated = EngineerTaskDelegation::find($delegation_id)?->update([
                'status' => self::IN_PROGRESS,
                'travel_duration' => $hours . ' hrs ' . $mins . 'mins'
            ]);

            if (!$updated) {
                throw new Exception('Failed to update Engineer Task Delegation status');
            }


            /** Update PM status */
            $pm = CorrectiveMaintenance::find($id);
            if (!$pm) {
                throw new Exception('PM record not found');
            }
            $updateStatus = $pm->update([
                'status' => self::IN_PROGRESS
            ]);
            if (!$updateStatus || !$pm->wasChanged('status')) {
                throw new Exception('Failed to update PM status');
            }

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }





    /** ============================= PM Task Processing ===================================== */
    public function mark_as_completed(Request $request)
    {
        $status_after_service =  $request->status_after_service;
        $actions_taken =  $request->fields;
        $spareparts =  $request->spareparts;
        $remarks =  $request->remarks;
        $complaint =  $request->complaint ?? '';
        $problem =  $request->problem ?? '';
        $delegation_id =  $request->delegation_id;
        $id =  $request->id;
        $date_now = Carbon::now();

        try {
            DB::beginTransaction();

            $this->checkIfRequestExist($id);

            $pm_data = CorrectiveMaintenance::find($id); //Get pm data for updating specific record
            if (!$pm_data) {
                return response()->json([
                    'error' => 'CM record not found',
                ], 404);
            }
            $get_latest_status = $pm_data->status;
            $withEquipment = PM::with(['equipment'])->find($id);
            $monitoring_end = null;

            if ($get_latest_status == PM::InProgress) {

                if ($status_after_service  == PM::further_monitoring) $tag = PM::pm_tag_set_observation;

                if ($status_after_service  == PM::non_operational) {
                    $tag = PM::pm_tag_backlog;
                    /** Create CM Schedule [if Status After Service is Non Operational] */
                    CorrectiveMaintenance::create([
                        'equipment_peripheral_id' => $pm_data->equipment_peripheral_id,
                        'service_id' => $pm_data->service_id,
                        'item_id' => $pm_data->item_id,
                        'institution' => $pm_data->institution,
                        'status' => PM::ReadyForDelegation
                    ]);
                }

                if ($status_after_service  == PM::operational) { //if this is operational
                    // $daysToAdd = $work_type === 'PM' ? 30 : 10;
                    $daysToAdd = 10;
                    $monitoring_end = Carbon::now()->startOfDay()->addDays($daysToAdd);
                    $tag = PM::pm_tag_under_observation;
                }


                $updatePM = $pm_data->update([
                    'status' => PM::Completed
                ]);
                if (!$updatePM || !$pm_data->wasChanged()) {
                    throw new Exception('Error updating PM record');
                }


                /** Update Task Delegation to Complete */
                $q_delegation_update = EngineerTaskDelegation::find($delegation_id);
                $update_delegation = $q_delegation_update->update([
                    'status_after_service' => $status_after_service,
                    'complaint' => $complaint,
                    'problem' => $problem,
                    'monitoring_end' => $monitoring_end,
                    'tag' => $tag,
                    'sr_remarks' => $remarks,
                    'status' => self::COMPLETED,
                ]);
                if (!$update_delegation || !$q_delegation_update->wasChanged()) {
                    throw new Exception('Error updating delegation');
                }

                /** Log Engineer Activities */
                $this->task_delegation->task_activities(
                    $delegation_id,
                    EngineerActivities::Ended,
                    Carbon::now(),
                    self::CM
                );


                /** Actions Taken */
                $dataToInsert = [];
                foreach ($actions_taken as $action) {
                    $dataToInsert[] = [
                        'service_id' => $delegation_id,
                        'action' => $action['action'],
                        'work_type' => self::CM
                    ];
                }
                $actions = $this->action->declare_actions_done($dataToInsert);
                if($actions !== true) throw new Exception("Error adding actions");


                /** Spareparts Used */
                $sparePartsToInsert = [];
                foreach ($spareparts as $parts) {
                    $sparePartsToInsert[] = [
                        'service_id' => $delegation_id,
                        'item_id' => $parts['item_id'],
                        'qty' =>(int) $parts['qty'],
                        'dr' => $parts['dr'],
                        'si' => $parts['si'],
                        'remarks' => $parts['remarks'],
                        'type' => self::CM
                    ];
                }
                $add_spareparts = $this->action->declare_spareparts_used($sparePartsToInsert);
                if($add_spareparts !== true) throw new Exception("Error adding spareparts");


                /********** Customer Details and Signature ************/
                $signatureData = $request->signature ?? null;
                $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
                $signatureData = str_replace(' ', '+', $signatureData);
                //Decode
                $signatureImage = base64_decode($signatureData);

                $filename = 'signatures/' . $id . '-' . Carbon::today()->format('m-d-y') . '.png';

                Storage::disk('public')->put($filename, $signatureImage);

                $add_customer = CustomerDetails::create([
                    'service_id' => $delegation_id,
                    'name' => $request->name,
                    'designation' => $request->designation,
                    'signature' => $filename,
                    'type' => self::CM,
                ]);
                if(!$add_customer) throw new Exception('Error adding customer details');
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }








    /** Soft Delete PM */
    public function delete_cm($id)
    {
        return $this->soft_delete_data(CorrectiveMaintenance::class, $id);
    }
}
