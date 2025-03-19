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
                'id' => ['table' => 'internal_request', 'db' => 'mysql', 'column' => 'id'],
                'service_id' => ['table' => 'eh', 'db' => 'mysql', 'column' => 'id'],
                'name' => ['table' => 'i', 'db' => 'mysql', 'column' => 'name'],
                'proposed_deleivery_date' => ['table' => 'eh', 'db' => 'mysql', 'column' => 'proposed_delivery_date'],
                'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
                'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
                'status' => ['table' => 'internal_request', 'db' => 'mysql', 'column' => 'status'],
            ];

            /**Server Mode Details */
            $current_page = $request->current_page ?? 1;
            $pageSize = $request->pagesize ?? 10;
            $search = $request->search ?? '';
            $sortColumn = $request->sort_column ?? '';
            $sortDirection = $request->sort_direction ?? '';


            $query = InternalServicingModel::with([
                'equipment_handling.equipments.master_data',
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


            /** =============================================================== */


            if ($this->role_validator->userHasRole($current_role)) {
                $query->where(function ($subQuery) use ($current_role, $user_id) {
                    if ($current_role === Roles::TLRoleID || $current_role === Roles::SBUAssistantRoleID) {
                        // $query->where(function ($subQuery) use ($user_id) {
                        $subQuery->orWhere('internal_request.current_assigned_by', '=', $user_id);
                        // });
                    } elseif ($current_role === Roles::engineerRoleID) {
                        // $query->where(function ($subQuery) use ($user_id) {
                        $subQuery->orWhere('internal_request.current_assigned_to', '=', $user_id);
                        // });
                    }
                });

                /** Filtering */
                if ($request->has('filterStatus')) {
                    $status = $request->filterStatus;
                    $query->whereIn('internal_request.status', $status);
                }
                if ($request->has('filterInstitution')) {
                    $institution_ids = $request->filterInstitution;
                    $institutions = array_map(function ($institution_ids) {
                        return $institution_ids['institution_id'];
                    }, $institution_ids ?? []);
                    $query->whereIn('eh.institution', $institutions);
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
                // 'test_data' => $status,
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
            $data = InternalServicingModel::with(['task_delegation' => function ($q) {
                    $q->with(['items_acquired'])
                        ->with(['task_activity'])
                        ->with(['actions_taken'])
                        ->with(['spareparts' => function($q){
                            $q->where('type', self::IS)
                                    ->with('equipment');
                        }])
                        ->where('type', self::IS);
                }])

                ->with(['equipment_handling' => function ($q) {
                    $q->with(['users']);
                    $q->with(['institution']);
                    $q->with(['equipments' => function ($q) {
                        $q->with(['master_data']);
                        $q->with(['general_master_data']);
                    }]);    // Get all equipments
                    $q->select(
                        'equipment_handling.*',
                        'u.first_name',
                        'u.last_name',
                        'i.name',
                        'i.address',
                    )
                        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
                        ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id');
                }])
                ->with(['task_delegation_all' => function ($q) {
                    $q->select(
                        'engineer_task_delegation.*',
                        DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    )->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'engineer_task_delegation.assigned_to', '=', 'assigned_to.id');
                    $q->with(['items_acquired']);
                    $q->with(['task_activity']);
                    $q->with(['actions_taken']);
                    $q->with(['spareparts']);
                }])
                ->select(
                    'internal_request.*',
                    DB::raw("CONCAT(assigned_to.first_name,' ',assigned_to.last_name) as assigned_to"),
                    DB::raw("CONCAT(assigned_by.first_name,' ',assigned_by.last_name) as assigned_by"),
                )
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_to', 'internal_request.current_assigned_to', '=', 'assigned_to.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as assigned_by', 'internal_request.current_assigned_by', '=', 'assigned_by.id')
                ->find($id);

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
        $service_id = $request->service_id;
        $delegation_id = $request->delegation_id;
        try {
            DB::beginTransaction();

            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'status' => self::SENTWAREHOUSE,
                'option_type' => InternalServicingModel::OPTION_TYPE_STORAGE
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
        $option_type = $request->option_type;
        $service_id = $request->service_id;

        try {
            DB::beginTransaction();
            if ($option_type == InternalServicingModel::OPTION_TYPE_STORAGE) {
                $main_status = EhServicesModel::FOR_STORAGE;
                $internal_status = self::STORAGE_TEXT;
                $level = null;
            } else {
                $main_status = EhServicesModel::ONGOING;
                $internal_status = self::COMPLETED;
                $level = EhServicesModel::BILLING_WIM;
            }

            $q_eh = EhServicesModel::find($service_id);
            $update_eh = $q_eh->update([
                'level' => $level,
                'main_status' => $main_status
            ]);
            if (!$update_eh) throw new Exception('Failed to update equipment handling');


            // Internal Request
            $q = InternalServicingModel::find($internal_id);
            $update_internal_status = $q->update([
                'status' => $internal_status
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
                Carbon::now(),
                'none_user_id'
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


            $update_delegation_status = $this->task_log->update_task_delegation_log(
                $internal_id,
                self::DELEGATED,
                self::IS,
                self::ACCEPTED,
                $remarks
            );

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

            $update_delegation_status = $this->task_log->update_task_delegation_log(
                $internal_id,
                self::DELEGATED,
                self::IS,
                self::DECLINED,
                $remarks,
                0 // set active status to 0
            );

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
        $remarks =  $request->remarks;
        $complaint =  $request->complaint ?? '';
        $problem =  $request->problem ?? '';
        $actions_taken = $request->fields ?? [];
        $items = $request->items ?? [];
        $spareparts = $request->spareparts ?? [];

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

            $actions = $this->action->declare_actions_done($dataToInsert);
            if ($actions !== true) throw new Exception("Error adding actions done");

            $statusInternal = ($status_after_service == 'Operational') ? self::SENTWAREHOUSE : self::RETURNEDHEAD;

            //Update Internal Status
            $updateIS = InternalServicingModel::where('id', $internal_id)->update([
                'status' => $statusInternal,
            ]);
            if (!$updateIS) throw new Exception("Error updating Internal servicing table");

            /** Submit Selected Items */
            $itemsToInsert = [];
            foreach ($items as $item) {
                $itemsToInsert[] = [
                    'service_id' => $delegation_id,
                    'item' => $item['item'],
                    'qty' => $item['qty'],
                    'remarks' => $item['remarks'],
                    'work_type' => self::IS
                ];
            }
            ChecklistItemAcquired::insert($itemsToInsert);


            $update_delegation = EngineerTaskDelegation::where('id', $delegation_id)->first()?->update([
                'option_type' => $option_type,
                'status_after_service' => $status_after_service,
                'complaint' => $complaint,
                'problem' => $problem,
                'sr_remarks' => $remarks,
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


            /** Spareparts Used */
            $sparePartsToInsert = [];
            foreach ($spareparts as $parts) {
                $sparePartsToInsert[] = [
                    'service_id' => $delegation_id,
                    'item_id' => $parts['item_id'],
                    'qty' => (int) $parts['qty'],
                    'dr' => $parts['dr'],
                    'si' => $parts['si'],
                    'remarks' => $parts['remarks'],
                    'type' => self::IS
                ];
            }
            $add_spareparts = $this->action->declare_spareparts_used($sparePartsToInsert);
            if ($add_spareparts !== true) throw new Exception("Error adding spareparts");


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
