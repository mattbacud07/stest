<?php

namespace App\Http\Controllers\Pullout;

use App\Http\Controllers\Controller;
use App\Models\Pullout as PM; //PulloutModel
use App\Traits\GlobalVariables;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ehController\WorkOrder;
use App\Models\ApprovalConfigModel;
use App\Models\EhServicesModel;
use App\Models\PulloutDecisionLog;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Services\ApprovalService;
use App\Services\Pullout\PulloutService;
use App\Services\Validation\UserRolesValidator;
use Carbon\Carbon;

class Pullout extends Controller
{
    use GlobalVariables;
    public $approval_service;
    public $user_role_validator;
    protected $pullout_service;

    public function __construct(ApprovalService $serviceApproval, UserRolesValidator $user_role_validator, PulloutService $pullout_service)
    {
        $this->approval_service = $serviceApproval;
        $this->user_role_validator = $user_role_validator;
        $this->pullout_service = $pullout_service;
    }

    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $current_role = (int) $request->current_role ?? 0;
        try {

            /** Search Column */
            $columnMappings = [
                'name' => ['table' => 'i', 'db' => 'mysql', 'column' => 'name'],
                'address' => ['table' => 'i', 'db' => 'mysql', 'column' => 'address'],
                'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
                'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
            ];

            /**Server Mode Details */
            $current_page = $request->current_page ?? 0;
            $pageSize = $request->pagesize ?? 0;
            $search = $request->search ?? '';
            $sortColumn = $request->sort_column ?? '';
            $sortDirection = $request->sort_direction ?? '';


            $query = PM::select(
                'pullout.*',
                DB::raw('CONCAT(u.first_name, " ", u.last_name) as requestedBy'),
                'i.name', //institution name
                'i.address',
            )
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'pullout.requested_by', '=', 'u.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'pullout.institution', '=', 'i.id')
                ->with(['roleUser.supervisor']);
            // ->get();

            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }

            if ($this->user_role_validator->userHasRole($current_role)) {
                // if ($current_role === Roles::approverRoleID) {
                //     $assigned_levels = $this->user_role_validator->getUserApprovalLevel(PM::approver_category);
                //     if(!empty($assigned_levels)){
                //         $query->where(function ($query) use ($assigned_levels, $user_id) {
                //             foreach ($assigned_levels as $level) {
                //                 if ($level == 1) {
                //                     $query->orWhere(function ($subQuery) use ($user_id) {
                //                         $subQuery->where('pullout.level', PM::SUPERVISOR)
                //                             ->whereExists(function ($existQuery) use ($user_id) {
                //                                     $existQuery->select(DB::raw(1))
                //                                         ->from('role_user as ru')
                //                                         ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'ru.user_id', 'u.id')
                //                                         ->where('ru.supervisor_id', $user_id)
                //                                         ->whereColumn('u.id', 'pullout.requested_by');
                //                             });
                //                     });
                //                 } //add more if neccessary
                //                 elseif($level == 2){
                //                     $query->orWhere(function($subQuery) use($user_id){
                //                         //for Operations Dept
                //                         $subQuery->whereExists(function($existQuery) use($user_id){
                //                             $existQuery->select(DB::raw(1))
                //                                 ->from('role_user as ru')
                //                                 ->join(DB::connection('mysqlSecond')->getDatabaseName() .'.users as u','ru.user_id','u.id')
                //                                 ->join('service_master_data as smd', 'smd.id', '=', 'pullout.equipment_id')
                //                                 ->where('u.department', PM::operations_dept)
                //                                 ->whereColumn('ru.satellite');
                //                         });
                //                 });
                //                 }
                //                 else {
                //                     $query->orWhere('pullout.level', $level);
                //                 }
                //             }
                //         });
                //     }else $query->whereRaw('1 = 0');

                // } elseif ($current_role === Roles::engineerRoleID) {
                //     $query->where('pullout.level', 'SUBJECT_TO_CHANGE');
                // } elseif ($current_role === Roles::requestorID) {
                //     $query->where('pullout.requested_by', $user_id);
                // }
                $pullout_data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                    $pageSize,
                    ['*'],
                    'page',
                    $current_page
                );
            } else return response()->json(['error' => 'Forbidden'], 403);

            return response()->json([
                'pullout_request' => $pullout_data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }




    /** View Specific ID */
    public function view(Request $request)
    {
        try {
            $request_id = $request->id;
            $data = PM::with('equipments')
            ->with(['equipments.general_master_data'])
            ->with(['equipments.master_data'])
            ->with(['pullout_decision_outbound' => function ($q) {
                $q->latest();
            }])
                ->with(['pullout_decision_service' => function ($q) {
                    $q->latest();
                }])
                ->where('pullout.id', $request_id)
                ->select(
                    'pullout.*',
                    'pullout.requested_by as requestor_id',
                    DB::raw('CONCAT(u.first_name, " ", u.last_name) as requested_by'),
                    'i.name as institution', //institution name
                    'i.address'
                )
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'pullout.requested_by', '=', 'u.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'pullout.institution', '=', 'i.id')
                ->first();
            if ($data) {
                $requestor_id = $data->requestor_id;
                $get_supervisor = RoleUser::where('user_id', $requestor_id)->where('role_id', Roles::requestorID)->value('supervisor_id');
                $has_schedule = PulloutDecisionLog::where('service_id', $data->id)->exists();
                $data->has_schedule = $has_schedule; 
                $data->pending_decisions = $data->counts_pending_decision();
                $data->supervisor = $get_supervisor;
            }
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }





    /** Create Request */
    public function create(Request $request)
    {
        $work_order = new WorkOrder();
        try {
            DB::beginTransaction();

            $equipments = $request->equipments ?? [];

            $user_id = Auth::user()->id;
            $data = [
                'institution' => $request->institution['institution_id'],
                'requested_by' => $user_id,
                'proposed_pullout_date' => $request->proposed_pullout_date,
                'level'  => PM::SUPERVISOR,
                'status'  => self::PENDING,
            ];

            $create = PM::create($data);
            $new_request_id = $create->id;
            if (!$create) {
                throw new Exception('Unable to create pullout request');
            }

            if (empty($equipments)) throw new Exception('Empty array of equipments');

            $work_order->submit_equipments($equipments, $new_request_id, 'Equipment', self::PULLOUT);

            /** Create Initial Approver Logs */
            $this->approval_service->logApproval($new_request_id, PM::SUPERVISOR, self::PULLOUT);

            DB::commit();

            return response()->json([
                'success' => true,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    /** APPROVE REQUEST FROM PULLOUT */
    public function approve(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_department = Auth::user()->department;
        $service_id = $request->service_id ?? null;
        $remark = $request->remark;
        $request_level = $request->level;
        $date_now = Carbon::now();
        $match = false;
        $underOperationService = false;

        try {
            DB::beginTransaction();

            /** Get the current approval level (level) for the request in the equipment handling table */
            $user_approver_level = ApprovalConfigModel::where('user_id', $user_id)
                ->where('approver_category', EhServicesModel::EH_PULLOUT)
                ->value('approval_level');


            $level = PM::where('id', $service_id)->value('level');

            // Add checks to ensure the values were found
            if (is_null($user_approver_level) || is_null($level)) {
                return response()->json(['errorLog' => 'Approver level or status not found']);
            }

            // Check if the level is match to proceed the approval
            if ($level != $request_level) {
                return response()->json(['errorLog' => 'Approval level does not match']);
            }

            if ($level == PM::OPERATION_SERVICE) {
                $type = null;
                if ($user_department == EhServicesModel::operation_department) {
                    $type = 'outbound';
                }
                if ($user_department == EhServicesModel::service_department) {
                    $type = 'service';
                }
                PulloutDecisionLog::create([
                    'assigned_by' => $user_id,
                    'service_id' => $service_id,
                    'scheduled_date' => $request->scheduled_date,
                    'preferred_schedule' => $request->preferred_schedule,
                    'remarks' => $request->remarks,
                    'status' => self::PENDING,
                    'type' => $type,
                ]);

                // Fetch the latest schedules for both departments
                $latest_service = PulloutDecisionLog::where('service_id', $service_id)
                    ->where('type', 'service')
                    ->where('status', self::PENDING)
                    ->latest()
                    ->first();

                $latest_outbound = PulloutDecisionLog::where('service_id', $service_id)
                    ->where('type', 'outbound')
                    ->where('status', self::PENDING)
                    ->latest()
                    ->first();

                // If the latest schedules match, mark all pending as complete
                if (
                    $latest_service && $latest_outbound
                    && $latest_service->scheduled_date === $latest_outbound->scheduled_date
                ) {
                    PM::where('id', $service_id)->first()?->update([
                            'final_schedule' => $latest_service->scheduled_date,
                            'status' => self::UNINSTALLING
                        ]);

                    PulloutDecisionLog::where('service_id', $service_id)
                        ->where('status', 'Pending')
                        ->update(['status' => 'Agreed']);
                        
                    $match = true;
                }
                
                $underOperationService = true;
            } else {
                $nextApproval = $this->pullout_service->pulloutNextApprovalLevel($level);
                [$next_approver, $status] = $nextApproval;


                $update_pullout = $this->pullout_service->approve_request($service_id, $next_approver, null);

                $update_log_approval = $this->approval_service->updateLogApproval(
                    $service_id,
                    $level,
                    self::PENDING, //status of approval_log
                    self::PULLOUT,
                    self::APPROVED, // status to update or new status
                    $remark,
                    $date_now
                );

                if ($update_pullout && $update_log_approval) {
                    /** Create Initial Approver Logs */
                    $this->approval_service->logApproval(
                        $service_id,
                        $next_approver,
                        self::PULLOUT
                    );
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'matched' => $match,
                'under_operation_service' => $underOperationService,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
