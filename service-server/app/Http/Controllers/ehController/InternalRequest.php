<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItemAcquired;
use App\Models\EhServicesModel;
use App\Models\EngineerActivities;
use App\Models\EngineerTaskDelegation;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Models\WorkOrder\InternalRequest as InternalServicingModel;
use App\Services\ActionsDoneService;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ApprovalService;
use App\Services\TaskDelegationService;
use App\Services\Validation\UserRolesValidator;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\map;

class InternalRequest extends Controller
{
    use GlobalVariables;
    protected $action;
    protected $task_log;
    protected $role_validator;
    public function __construct(
        ActionsDoneService $actions,
        TaskDelegationService $task_log,
        UserRolesValidator $role_validator
    ) {
        $this->action = $actions;
        $this->task_log = $task_log;
        $this->role_validator = $role_validator;
    }


    /**
     * Get All Internal Servicing Request
     */
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $current_role = (int) $request->current_role ?? 0;
        try {

            /** Search Column */
            $columnMappings = [
                'name' => ['table' => 'i', 'db' => 'mysql', 'column' => 'name'],
                'proposed_deleivery_date' => ['table' => 'eh', 'db' => 'mysql', 'column' => 'proposed_delivery_date'],
                'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
                'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
                'status' => ['table' => 'internal_request', 'db' => 'mysql', 'column' => 'status'],
            ];

            /**Server Mode Details */
            $current_page = $request->current_page ?? 0;
            $pageSize = $request->pageSize ?? 0;
            $search = $request->search ?? '';
            $sortColumn = $request->sort_column ?? '';
            $sortDirection = $request->sort_direction ?? '';


            $query = InternalServicingModel::with([
                'equipment_handling.equipments',
            ])
                ->select(
                    'internal_request.*',
                    'i.name as institution',
                    'eh.proposed_delivery_date',
                    DB::raw("CONCAT(u.first_name,' ',u.last_name) as requested_by"),
                    DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    DB::raw("CONCAT(assigned_by.first_name,' ',assigned_by.last_name) as assigned_by"),
                )
                ->leftjoin('equipment_handling as eh', 'internal_request.service_id', '=', 'eh.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'eh.institution', '=', 'i.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'eh.requested_by', '=', 'u.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'internal_request.current_assigned_to', '=', 'assigned_to.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_by', 'internal_request.current_assigned_by', '=', 'assigned_by.id');


            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }

            if ($this->role_validator->userHasRole($current_role)) {
                if ($current_role === Roles::TLRoleID) {
                    $query->orWhere(function ($subQuery) use ($user_id) {
                        $subQuery->where('internal_request.current_assigned_by', '=', $user_id);
                    });
                } elseif ($current_role === Roles::engineerRoleID) {
                    $query->orWhere(function ($subQuery) use ($user_id) {
                        $subQuery->where('internal_request.current_assigned_to', '=', $user_id);
                    });
                }
                $data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                    $pageSize,
                    ['*'],
                    'page',
                    $current_page
                );
            } else return response()->json(['error' => 'Forbidden'], 403);

            return response()->json([
                'internal_servicing_data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    /** Get Internal Row By ID */
    public function getInternalRowById(Request $request)
    {
        $id = $request->id;
        $user_id = Auth::user()->id;

        try {
            $data = InternalServicingModel::from('internal_request as it')
                ->with(['task_delegation.task_activity'])
                ->with(['task_delegation.actions_taken'])
                ->with(['equipment_handling' => function ($q) {
                    $q->select(
                        'equipment_handling.*',
                        'u.first_name',
                        'u.last_name',
                        'i.name',
                        'i.address', 
                    )
                        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
                        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
                        ->get();
                }])
                ->with(['task_delegation_all' => function ($q) {
                    $q->select(
                        'engineer_task_delegation.*',
                        DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    )->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'engineer_task_delegation.assigned_to', '=', 'assigned_to.id');
                }])
                // ->with(['task_delegation'])
                ->select(
                    'it.*',
                    DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    DB::raw("CONCAT(assigned_by.first_name,' ',assigned_by.last_name) as assigned_by"),
                )
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'it.current_assigned_to', '=', 'assigned_to.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_by', 'it.current_assigned_by', '=', 'assigned_by.id')
                ->where('it.id', $id)->first();

            return response()->json([
                'internal_request' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }



    /**
     * Submit Internal Servicing Request
     */
    public function add_internal_process(Request $request)
    {
        $service_id = $request->service_id ?? null;
        $user_id = Auth::user()->id;
        /** Check if service_id already exist in Internal Request table */
        $check_serviceId = InternalServicingModel::where('service_id', $service_id)->first();
        if ($check_serviceId) {
            return response()->json([
                'exist_service_id' => true,
            ]);
        }

        /**Check Status for delegation of Internal Servicing */
        $eh_current_level = EhServicesModel::where('id', $service_id)->value('level');
        if ($eh_current_level !== EhServicesModel::SERVICE) {
            throw new Exception('The current level is not eligible for creating an internal request');
        }

        $data = [
            'service_id' => $service_id,
            'current_assigned_to' => $request->assigned_to,
            'current_assigned_by' => $user_id,
            'status' => self::DELEGATED
        ];

        try {
            DB::beginTransaction();
            $add_internal_request = InternalServicingModel::create($data);
            $internal_servicing_id  = $add_internal_request->id;
            if (!$add_internal_request) {
                throw new Exception('Error creating internal request.');
            }

            $updateEH = EhServicesModel::find($service_id);
            $update_status = $updateEH->update([
                'main_status' => EhServicesModel::INTERNAL_SERVICING,
            ]);
            if (!$update_status) {
                throw new Exception('Error updating status.');
            }

            /** Create Task Log for Task Delegation */
            $this->task_log->task_delegation_log(
                $internal_servicing_id,
                $request->assigned_to,
                $user_id,
                self::DELEGATED,
                self::IS,
                null,
                null,
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    /** Redelegation of Service */
    public function redelegation(Request $request)
    {
        $user_id = Auth::user()->id;
        $internal_id = $request->internal_id;
        $delegation_id = $request->delegation_id ?? null;
        try {
            DB::beginTransaction();

            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'current_assigned_to' => $request->assigned_to,
                'status' => self::DELEGATED
            ]);
            if (!$update_internal_status) throw new Exception('Failed to update internal status');

            if ($delegation_id) {
                $query = EngineerTaskDelegation::find($delegation_id);
                if ($query) {
                    $qUpdateActive = $query->update([
                        'active' => 0
                    ]);
                    if (!$qUpdateActive) throw new Exception('Failed to update delegation status');
                }
            }

            /** Create Task Log for Task Delegation */
            $this->task_log->task_delegation_log(
                $internal_id,
                $request->assigned_to,
                $user_id,
                self::DELEGATED,
                self::IS,
                null,
                null,
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }



    /** For Storage Process */
    public function for_storage(Request $request)
    {
        $internal_id = $request->internal_id;
        try {
            DB::beginTransaction();

            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'status' => self::SENTWAREHOUSE
            ]);
            if (!$update_internal_status) throw new Exception('Failed to update internal status');

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }



    /** Approve By WIM Personnel */
    public function approve_by_wim(Request $request)
    {
        $approvalService = new ApprovalService();

        $internal_id = $request->internal_id;
        $service_id = $request->service_id;

        try {
            DB::beginTransaction();

            $q_eh = EhServicesModel::find($service_id);
            $update_eh = $q_eh->update([
                'level' => EhServicesModel::BILLING_WIM,
                'main_status' => EhServicesModel::ONGOING
            ]);
            if (!$update_eh) throw new Exception('Failed to update equipment handling');


            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'status' => self::COMPLETED
            ]);
            if (!$update_internal_status) throw new Exception('Failed to update internal status');


            $approvalService->logApproval(
                $service_id,
                EhServicesModel::BILLING_WIM,
                self::EH
            );


            $approvalService->updateLogApproval(
                $service_id,
                EhServicesModel::SERVICE,
                self::PENDING, //status of approval_log
                self::EH,
                self::APPROVED, // status to update or new status
                null,
                Carbon::now()
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }




    /** Accept Internal Request */
    public function accept_internal_request(Request $request)
    {
        $internal_id = $request->internal_id;
        $delegation_id = $request->delegation_id;
        $remarks = $request->remarks;
        try {
            DB::beginTransaction();

            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'status' => self::IN_PROGRESS
            ]);
            if (!$update_internal_status) throw new Exception('Failed to update internal status');

            // Task Delegation
            $update_delegation_status = EngineerTaskDelegation::where([
                'service_id' => $internal_id,
                'status' => self::DELEGATED,
                'type' => self::IS,
                'active' => 1,
            ])->update([
                'status' => self::ACCEPTED,
                'remarks' => $remarks,
                'acted_at' => Carbon::now()
            ]);

            if (!$update_delegation_status) throw new Exception('Failed to update delegation status');
            /** Create Task Log for Task Delegation */
            $this->task_log->task_activities(
                $delegation_id,
                EngineerActivities::Started,
                Carbon::now(),
                self::IS
            );

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }


    /** Decline Internal Request */
    public function decline_internal_request(Request $request)
    {
        $internal_id = $request->internal_id;
        $remarks = $request->remarks;
        try {
            DB::beginTransaction();

            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'current_assigned_to' => null,
                'status' => self::DECLINED
            ]);
            if (!$update_internal_status) throw new Exception('Failed to update internal status');

            // Task Delegation
            $update_delegation_status = EngineerTaskDelegation::where([
                'service_id' => $internal_id,
                'status' => self::DELEGATED,
                'type' => self::IS,
                'active' => 1,
            ])->update([
                'status' => self::DECLINED,
                'remarks' => $remarks,
                'acted_at' => Carbon::now(),
                'active' => 0
            ]);

            if (!$update_delegation_status) throw new Exception('Failed to update delegation status');


            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }



    /** Internal Servicing Request Completed */
    public function internal_servicing_completed(Request $request)
    {
        $approvalService = new ApprovalService();

        $internal_id = $request->internal_id;
        $delegation_id = $request->delegation_id;
        $service_id = $request->service_id ?? null;
        $option_type = $request->option_type;
        $status_after_service = $request->status_after_service;
        $actions_taken = $request->actions_taken ?? [];
        $items = $request->items ?? [];

        $request->validate([
            'option_type' => 'required',
            'status_after_service' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $dataToInsert = [];
            foreach ($actions_taken as $action) {
                $dataToInsert[] = [
                    'service_id' => $delegation_id,
                    'action' => $action['action'],
                    'work_type' => self::IS
                ];
            }

            $submitAction = $this->action->declare_actions_done($dataToInsert);
            if (!$submitAction) {
                throw new Exception('Error adding actions Done');
            }

            $statusInternal = ($status_after_service == 'Operational') ? self::SENTWAREHOUSE : self::RETURNEDHEAD;

            //Update Internal Status
            InternalServicingModel::where('id', $internal_id)->update([
                'status' => $statusInternal,
            ]);

            /** Submit Selected Items */
            $itemsToInsert = [];
            foreach ($items as $item) {
                $itemsToInsert[] = [
                    'service_id' => $internal_id,
                    'item' => $item['item'],
                    'qty' => $item['qty'],
                    'remarks' => $item['remarks'],
                ];
            }
            $insert_items = ChecklistItemAcquired::insert($itemsToInsert);
            if (!$insert_items) {
                throw new Exception('Error inserting items');
            }


            $update_delegation = EngineerTaskDelegation::where('id', $delegation_id)->update([
                'option_type' => $option_type,
                'status_after_service' => $status_after_service,
                'status' => self::COMPLETED,
            ]);
            if (!$update_delegation) {
                throw new Exception('Error updating delegation');
            }

            $this->task_log->task_activities(
                $delegation_id,
                EngineerActivities::Ended,
                Carbon::now(),
                self::IS
            );

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
