<?php

namespace App\Http\Controllers\ehController;

use App\Http\Controllers\Controller;
use App\Models\EhServicesModel;
use App\Models\Roles;
use App\Models\RoleUser;
use App\Models\WorkOrder\InternalRequest as WOInternalRequest;
use App\Services\ActionsDoneService;
use App\Traits\GlobalVariables;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\map;

class InternalRequest extends Controller
{
    use GlobalVariables;
    protected $action;
    public function __construct(ActionsDoneService $actions)
    {
        $this->action = $actions;
    }

    /**
     * Get Engineers Data
     */
    public function get_engineers_data()
    {
        $role_name = Roles::engineerRole;
        $engineers = RoleUser::
        with(['users' => function($q){
            $q->select('id','first_name','last_name');
        }])
        ->whereHas('roles', function($q) use($role_name){
            $q->where('role_name', $role_name);
        })
        ->get();

        return response()->json([
            'engineers' => $engineers,
        ], 200);
    }

    /**
     * Get Internal Request Details 
     */
    public function get_internal_request_details(Request $request)
    {
        $requested_id = $request->requested_id;

        try {
            $getDetails = WOInternalRequest::select('internal_request.*', 'i.name as type_of_activity_name')->where('internal_request.id', $requested_id)
                ->leftJoin('internal_external_requests as i', 'internal_request.type_of_activity', '=', 'i.id')
                // ->lefttJoin(DB::connection('mysqlSecond')->getDatabaseName().'.users as u', 'internal_request.delegated_to', '=', 'u.id')
                ->get();

            if (!$getDetails) {
                throw new Exception('Something went wrong in fetching data');
            }
            return response()->json([
                'details' => $getDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }


    /** Check if service_id already exist in Internal Request table */
    // public function checkIfDelegated(Request $request)
    // {
    //     $check_serviceId = WOInternalRequest::where('service_id', $request->service_id)
    //         ->where('status', self::internalStat['Delegated'])
    //         ->first();
    //     $exist = null;
    //     if ($check_serviceId) {
    //         $exist = true;
    //     }
    //     return response()->json([
    //         'exist_service_id' => $exist,
    //     ]);
    // }



    /**
     * Get All Internal Servicing Request for Specific User
     */
    public function getInternalRequest(Request $request)
    {
        $user_id = $request->user_id;
        try {
            $get_request = WOInternalRequest::with(['equipment_handling' => function ($query) {
                $query->select(
                    'equipment_handling.id',
                    'institution',
                    'proposed_delivery_date',
                    'equipment_handling.created_at',
                    'i.name as institution_name'
                )
                    ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'i.id', '=', 'equipment_handling.institution');
            }])
                ->with('internal_external_name')
                ->with('getUser');


            if ($request->has('category') && $request->category === 'specificUser') {
                $get_request->where('delegated_to', $user_id);
            } elseif ($request->has('category') && $request->category === 'specificRow') {
                $get_request->where('id', $request->id);
            } elseif ($request->has('category') && $request->category === 'specificRowServicing') {
                $get_request->where('id', $request->id);
                $get_request->with(['actions_done' => function ($query) {
                    $query->where('work_type', 'Internal');
                }]);
                $get_request->with(['equipments' => function ($query) {
                    $query->select(
                        'equipment_peripherals.*',
                        'm.item_code',
                        'm.description'
                    )
                        ->join(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'm.id', '=', 'equipment_peripherals.item_id');
                    $query->where('category', 'Equipment');
                }]);
            } elseif ($request->has('category') && $request->category === 'specificService') {
                $get_request->where('service_id', $request->service_id);
            }



            /** Depends on Loggedin Roles */
            if($request->has('category') && $request->category === 'delegated_to'){
                $get_request->where('delegated_to', $user_id);
            }
            if($request->has('category') && $request->category === Roles::WIMRole){
                $get_request->where('status', self::internalStat['Packed']);
            }





            /** Data Filtering  */
            if($request->filled('filterStatus')){
                $get_request->whereIn('status', $request->filterStatus);
            }
            if($request->filled('delegation_date')){
                $startDate = Carbon::parse($request->delegation_date[0])->startOfDay();
                $endDate = Carbon::parse($request->delegation_date[1])->endOfDay();
                $get_request->whereBetween('delegation_date', [$startDate, $endDate] );
            }
            if($request->filled('filterInstitution')){
                $filterInstitution = array_map(function($data){
                    return $data['iId'];
                }, $request->filterInstitution);
                
                $get_request->whereHas('equipment_handling', function($q) use ($filterInstitution){
                    $q->whereIn('institution', $filterInstitution);
                });
            }
            $get_request = $get_request->get();

            // $queries = DB::getQueryLog();
            // Log::info($queries);
            return response()->json([
                'request' => $get_request,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }



    /**
     * Submit Internal Servicing Request
     */
    public function add_internal_process(Request $request)
    {
        /** Check if service_id already exist in Internal Request table */
        $check_serviceId = WOInternalRequest::where('service_id', $request->service_id)->first();
        if ($check_serviceId) {
            return response()->json([
                'exist_service_id' => true,
            ]);
        }
        $data = [
            'service_id' => $request->service_id,
            'received_by' => $request->received_by,
            'type_of_activity' => $request->type_of_activity,
            'other' => $request->other,
            'delegation_date' => Carbon::now(),
            'delegated_to' => $request->delegated_to,
            'status' => self::internalStat['Delegated']
        ];

        try {
            DB::beginTransaction();
            $add_internal_request = WOInternalRequest::create($data);
            if (!$add_internal_request) {
                throw new Exception('Error adding Internal Request');
            }
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
        $requested_id = $request->requested_id;
        $service_id = $request->service_id;
        try {
            DB::beginTransaction();
            $accept_request = WOInternalRequest::where('id', $requested_id)->update([
                'date_started' => Carbon::now(),
                'status' => self::internalStat['InProgress'],
            ]);
            if (!$accept_request) {
                throw new Exception('Error updating. Please refresh. ' . $requested_id);
            }
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
        $request_id = $request->requested_id;
        // $service_id = $request->service_id;

        $arrayOfData = $request->arrayOfData;

        $dataToInsert = [];
        foreach ($arrayOfData as $data) {
            $dataToInsert[] = [
                'serial' => $data['serial'],
                'equipment_id' => $data['equipment_id'],
                'work_type_id' => $data['work_type_id'],
                'service_id' => $data['service_id'],
                'action' => $data['action'],
                'work_type' => $data['work_type'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        try {
            DB::beginTransaction();
            $submitAction = $this->action->declare_actions_done($dataToInsert);
            if (!$submitAction) {
                throw new Exception('Error Adding Actions Done');
            }
            WOInternalRequest::where('id', $request_id)->update([
                'status' => self::internalStat['Completed'],
                'accomplished_date' => Carbon::now(),
            ]);
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


    public function markPackedEndorsedToWarehouse(Request $request)
    {
        $id = $request->requested_id;
        $service_id = $request->service_id;
        $status = $request->status;

        $dataToUpdate = [];

        try {
            DB::beginTransaction();

            if ($status === self::internalStat['Packed']) {
                $dataToUpdate = [
                    'accepted_by_warehouse' => Carbon::now(),
                    'status' => self::internalStat['ConfirmedByWIM']
                ];

                $updateEH = EhServicesModel::where('id', $service_id)
                    ->update([
                        'status' => EhServicesModel::SERVICE_HEAD_ENGINEER
                    ]);

                if (!$updateEH) {
                    throw new Exception('Error updating Equipment Handling Status');
                }
            }
            if ($status === self::internalStat['Completed']) {
                $dataToUpdate = [
                    'packed_endorse_to_warehouse' => 1,
                    'endorsement_date' => Carbon::now(),
                    'status' => self::internalStat['Packed']
                ];
            }
            $data = WOInternalRequest::where('id', $id)
                ->update($dataToUpdate);

            if (!$data) {
                throw new Exception('Error updating status Packed');
            }

            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
