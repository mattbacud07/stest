<?php

namespace App\Http\Controllers\EquipmentHandling;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\Roles;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use App\Services\EquipmentHandlingService\EHBasicOperation;
use App\Services\Validation\UserRolesValidator;
use App\Traits\GlobalVariables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentHandlingController extends Controller
{
    use GlobalVariables;
    protected $EHBasicOperation;
    protected $user_role_validator;
    public function __construct(
        EHBasicOperation $EHBasicOperation,
        UserRolesValidator $user_role_validator
    ) {
        $this->EHBasicOperation = $EHBasicOperation;
        $this->user_role_validator = $user_role_validator;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_department = Auth::user()->department;
        $user_sbu = RoleUser::where(['user_id' => $user_id, 'role_id' => Roles::approverRoleID])->value('sbu');
        $tl_assitant_sbu = RoleUser::where('user_id', $user_id)->whereIn('role_id', [Roles::TLRoleID, Roles::SBUAssistantRoleID])->value('sbu');
        $current_role = (int) $request->current_role ?? 0;
        try {

            /** Search Column */
            $columnMappings = [
                'id' => ['table' => 'equipment_handling', 'db' => 'mysql', 'column' => 'id'],
                'name' => ['table' => 'i', 'db' => 'mysql', 'column' => 'name'],
                'address' => ['table' => 'i', 'db' => 'mysql', 'column' => 'address'],
                'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
                'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
                'request_name' => ['table' => 'iE', 'db' => 'mysql', 'column' => 'name'],
            ];

            /**Server Mode Details */
            $current_page = $request->current_page ?? 0;
            $pagesize = $request->pagesize ?? 0;
            $search = $request->search ?? '';
            $sortColumn = $request->sort_column ?? '';
            $sortDirection = $request->sort_direction ?? '';


            $query = EhServicesModel::select(
                'equipment_handling.*',
                'i.name',
                'i.address',
                'i.area',
                'u.first_name',
                'u.last_name',
                'u.department',
                DB::raw("CONCAT(u.first_name,' ',u.last_name) as user_name"),
                'iE.name as request_name',
                // 'a.approver_level',
                // 'a.approver_name'
            )
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
                ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
                ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
                // ->leftjoin('approver_designation as a', 'equipment_handling.level', '=', 'a.approver_level')
                ->with(['equipments'])->with(['equipments.master_data']);


            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }



            if ($this->user_role_validator->userHasRole($current_role)) {
                if ($current_role === Roles::approverRoleID) {
                    $assigned_levels = $this->user_role_validator->getUserApprovalLevel(EhServicesModel::EH_INSTALLATION);
                    if (!empty($assigned_levels)) {
                        $query->where(function ($query) use ($assigned_levels, $user_department, $user_sbu) {
                            foreach ($assigned_levels as $level) {
                                if ($level == EhServicesModel::SM_SER) {
                                    $query->orWhere(function ($subQuery) use ($user_department, $user_sbu) {
                                        $subQuery->where('equipment_handling.level', EhServicesModel::SM_SER)
                                        ->where('equipment_handling.main_status', EhServicesModel::ONGOING)
                                            ->whereExists(function ($existQuery) use ($user_department, $user_sbu) {
                                                $existQuery->select(DB::raw(1))
                                                    ->from(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u')
                                                    ->whereColumn('u.id', 'equipment_handling.requested_by')
                                                    // Join the equipment_peripheral table to get the service_id
                                                    ->join('equipment_peripherals as ep', 'ep.service_id', '=', 'equipment_handling.id')
                                                    // Join the master_data table to get the sbu
                                                    ->join('service_master_data as md', 'md.id', '=', 'ep.service_master_data_id')
                                                    // ->where('u.department', $user_department)
                                                    ->where(function ($deptQuery) use ($user_department, $user_sbu) {
                                                        $deptQuery->when(
                                                            $user_department === EhServicesModel::service_department,
                                                            function ($serviceQuery) use ($user_sbu) {
                                                                $serviceQuery->where('u.department', EhServicesModel::service_department)
                                                                    ->where(function ($q) use ($user_sbu) {
                                                                        $q->where('md.sbu', $user_sbu) // Match the sbu from the master_data table
                                                                            ->orWhereExists(function ($query_manager) {
                                                                                $query_manager->select(DB::raw(1))
                                                                                    ->from('role_user')
                                                                                    ->where('user_id', Auth::user()->id)
                                                                                    ->where('role_id', Roles::NationalManagerID);
                                                                            });
                                                                    });
                                                            }
                                                        );
                                                        $deptQuery->when(
                                                            $user_department === EhServicesModel::sm_department,
                                                            function ($salesQuery) {
                                                                $salesQuery->where('u.department', EhServicesModel::sm_department);
                                                            }
                                                        );
                                                    });
                                            });
                                    });
                                } else if ($level === EhServicesModel::SERVICE) {
                                    $query->orwhere(function ($subQuery) use ($user_sbu) {
                                        $subQuery->where('equipment_handling.level', EhServicesModel::SERVICE)
                                        ->whereIn('equipment_handling.main_status', [EhServicesModel::ONGOING, EhServicesModel::INTERNAL_SERVICING])
                                            ->whereExists(function ($existQuery) use ($user_sbu) {
                                                $existQuery->select(DB::raw(1))
                                                    ->from('equipment_peripherals as ep')
                                                    ->join('service_master_data as md', 'md.id', '=', 'ep.service_master_data_id')
                                                    ->where('md.sbu', '=', $user_sbu)
                                                    ->where('ep.request_type','eh')
                                                    ->whereColumn('ep.service_id', 'equipment_handling.id')
                                                    ->orWhereExists(function ($query_manager) {
                                                        $query_manager->select(DB::raw(1))
                                                            ->from('role_user')
                                                            ->where('user_id', Auth::user()->id)
                                                            ->where('role_id', Roles::NationalManagerID);
                                                    });
                                            });
                                    });
                                } else {
                                    $query->orWhere('equipment_handling.level', $level)
                                    ->where('equipment_handling.main_status', EhServicesModel::ONGOING);
                                }
                            }
                        });
                    } else $query->whereRaw('1 = 0');
                } elseif ($current_role === Roles::TLRoleID || $current_role === Roles::SBUAssistantRoleID) {
                    $query->whereIn('equipment_handling.main_status', [
                        EhServicesModel::INSTALLING,
                        EhServicesModel::COMPLETE,
                    ]);
                    // $query->where('equipment_handling.level', EhServicesModel::INSTALLATION_TL);
                    $query->where(function ($q) use ($tl_assitant_sbu) {
                        $q->whereExists(function ($existQuery) use ($tl_assitant_sbu) {
                            $existQuery->select(DB::raw(1))
                                ->from('equipment_peripherals as ep')
                                ->join('service_master_data as md', 'md.id', '=', 'ep.service_master_data_id')
                                ->where('md.sbu', '=', $tl_assitant_sbu)
                                ->whereColumn('ep.service_id', '=', 'equipment_handling.id');
                        });
                    });
                } elseif ($current_role === Roles::engineerRoleID) {
                    $query->where('equipment_handling.main_status', EhServicesModel::INSTALLING);
                    $query->where(
                        fn($q) =>
                        $q->whereExists(
                            fn($existQuery) =>
                            $existQuery->select(DB::raw(1))
                                ->from('engineer_task_delegation as etd')
                                ->where('etd.assigned_to', '=', $user_id)
                                ->where('etd.active', '=', 1)
                                ->whereColumn('etd.service_id', '=', 'equipment_handling.id')
                        )
                    );
                } elseif ($current_role === Roles::requestorID) {
                    $query->where('equipment_handling.requested_by', $user_id);
                }

                /** Filtering */
                if ($request->has('filterStatus')) {
                    $status = $request->filterStatus ?? [];
                    $query->whereIn('equipment_handling.main_status', $status);
                }
                if ($request->has('filterInstitution')) {
                    $institution_ids = $request->filterInstitution;
                    $institutions = array_map(function ($institution_ids) {
                        return $institution_ids['institution_id'];
                    }, $institution_ids ?? []);
                    $query->whereIn('equipment_handling.institution', $institutions);
                }
                if ($request->has('filterApprovalLevel')) {
                    $level = $request->filterApprovalLevel ?? [];
                    $query->whereIn('equipment_handling.level', $level);
                }

                $data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                    $pagesize,
                    ['*'],
                    'page',
                    $current_page
                );
                // ->appends([
                //     'filterStatus' => $request->filterStatus ?? [],
                //     'filterInstitution' => $request->filterInstitution ?? [],
                // ]);
            } else return response()->json(['error' => 'Forbidden'], 403);

            return response()->json([
                'equipment_handling' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function getEquipmentHandlingById(Request $request)
    {
        try {
            $service_id = $request->service_id ?? 0;
            $module_type = '';
            if ($request->has('module_type')) $module_type = $request->module_type;

            // Find and return an equipment handling by its ID, or return null if not found
            $data = EhServicesModel::with(['users'])    // Get the user who requested the service
                ->with(['institution'])    // Get the institution where the service is requested
                ->with(['equipments' => function ($q) {
                    $q->with(['master_data']);
                    $q->with(['general_master_data']);
                }])    // Get all equipments
                ->with(['peripherals' => function ($q) {
                    $q->with(['master_data']);
                    $q->with(['general_master_data']);
                }])    // Get all peripherals
                ->with(['approval_logs' => function ($q) {
                    $q->with(['users']);
                    $q->with(['approvers' => function ($q) {
                        $q->with('users');
                    }]);
                }])    // Get all approval logs and approvers

                ->with(['task_delegation' => function ($q) use ($module_type) {
                    $q->with([
                        'task_activity' => function ($q) use ($module_type) {
                            $q->where('type', $module_type);
                        },
                        'actions_taken' => function ($q) use ($module_type) {
                            $q->where('work_type', $module_type);
                        },
                        'spareparts' => function ($q) use ($module_type) {
                            $q->where('type', $module_type)
                                ->with(['equipment']);
                        }
                    ]);
                }])


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
                ->select(
                    'equipment_handling.*',
                    'iE.name as request_name'
                )
                ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
                ->find($service_id);

            return response()->json([
                'request' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }


    public function delete_eh($id){
        return $this->soft_delete_data(EhServicesModel::class, $id);
    }
}
