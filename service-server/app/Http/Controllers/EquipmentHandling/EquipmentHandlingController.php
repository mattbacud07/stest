<?php

namespace App\Http\Controllers\EquipmentHandling;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\Roles;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use App\Services\EquipmentHandlingService\EHBasicOperation;
use App\Services\EquipmentHandlingService\EHTaskOperation;
use App\Services\Validation\UserRolesValidator;
use App\Traits\GlobalVariables;
use Illuminate\Contracts\Auth\Guard;
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
        $user_sbu = RoleUser::where(['user_id' => $user_id, 'role_id' => EhServicesModel::EH_INSTALLATION])->value('sbu');
        $current_role = (int) $request->current_role ?? 0;
        try {

            /** Search Column */
            $columnMappings = [
                'name' => ['table' => 'i', 'db' => 'mysql', 'column' => 'name'],
                'address' => ['table' => 'i', 'db' => 'mysql', 'column' => 'address'],
                'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
                'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
                'request_name' => ['table' => 'iE', 'db' => 'mysql', 'column' => 'name'],
            ];

            /**Server Mode Details */
            $current_page = $request->current_page ?? 0;
            $pageSize = $request->pageSize ?? 0;
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
                ->with(['equipments' => function ($q) {
                    $q->where('request_type', self::EH);
                    $q->where('category', 'Equipment');
                }]);

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
                                                                    ->where('md.sbu', $user_sbu); // Match the sbu from the master_data table
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
                                    $query->orWhere(function ($subQuery) use ($user_sbu) {
                                        $subQuery->where('equipment_handling.level', EhServicesModel::SERVICE)
                                            ->whereExists(function ($existQuery) use ($user_sbu) {
                                                $existQuery->select(DB::raw(1))
                                                    ->from('equipment_peripherals as ep')
                                                    ->join('service_master_data as md', 'md.id', '=', 'ep.service_master_data_id')
                                                    ->where('md.sbu', '=', $user_sbu)
                                                    ->whereColumn('ep.service_id','=','equipment_handling.id');
                                            });
                                    });
                                } else {
                                    $query->orWhere('equipment_handling.level', $level);
                                }
                            }
                        });
                    } else $query->whereRaw('1 = 0');
                } elseif ($current_role === Roles::TLRoleID) {
                    $query->where('equipment_handling.level', EhServicesModel::INSTALLATION_TL);   //SUBJECT_TO_CHANGE
                } elseif ($current_role === Roles::engineerRoleID) {
                    $query->where('equipment_handling.level', EhServicesModel::INSTALLATION_ENGINEER);   //SUBJECT_TO_CHANGE
                } elseif ($current_role === Roles::requestorID) {
                    $query->where('equipment_handling.requested_by', $user_id);
                }
                $data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                    $pageSize,
                    ['*'],
                    'page',
                    $current_page
                );
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
        $user = Auth::user();
        $roleUsers = RoleUser::leftjoin('roles', 'roles.roleID', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('role_user.*', 'roles.role_name', 'roles.roleID', 'roles.permissions') // Select the fields you need
            ->get();

        $service_id = $request->service_id ?? 0;

        // $data = $this->EHBasicOperation->getEquipmentHandlingByServiceId($service_id);
         // Find and return an equipment handling by its ID, or return null if not found
         $data = EhServicesModel::select(
            'equipment_handling.*', 
            'i.name', 'i.address', 
            'i.area', 'u.first_name', 
            'u.last_name', 'u.department', 
            'ui.first_name as fname', 
            'ui.last_name as lname', 
            'ua.first_name as assigned_tl_fname', 
            'ua.last_name as assigned_tl_lname', 
            'iE.name as request_name')
            ->where(['equipment_handling.id' => $service_id])
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'equipment_handling.requested_by', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ui', 'equipment_handling.installer', '=', 'ui.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as ua', 'equipment_handling.tl_assigned', '=', 'ua.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'equipment_handling.institution', '=', 'i.id')
            ->leftjoin('internal_external_requests as iE', 'equipment_handling.request_type', '=', 'iE.id')
            ->first();

        return response()->json([
            'request' => $data,
        ], 200);
    }
}
