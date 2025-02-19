<?php

namespace App\Http\Controllers;

use App\Models\MasterData as ModelsMasterData;
use App\Models\ServiceMasterData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterData extends Controller
{
    public function get_master_data(Request $request)
    {

        /** Search Column */
        $columnMappings = [
            'equipment' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'equipment'],
            'serial' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'serial'],
            'institution' => ['table' => 'i', 'db' => 'mysqlSecond', 'column' => 'name'],
            'sbu' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'sbu'],
            'dealer_name' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'dealer_name'],
            'frequency' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'frequency'],
            'status' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'status'],
            'admission_date' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'admission_date'],
            'date_installed' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'date_installed'],
            'contract_due_date' => ['table' => 'service_master_data', 'db' => 'mysql', 'column' => 'contract_due_date'],
            'item_code' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'item_code'],
            'description' => ['table' => 'm', 'db' => 'mysqlSecond', 'column' => 'description'],
            'first_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'first_name'],
            'last_name' => ['table' => 'u', 'db' => 'mysqlSecond', 'column' => 'last_name'],
        ];

        /**Server Mode Details */
        $current_page = $request->current_page ?? 0;
        $pageSize = $request->pagesize ?? 0;
        $search = $request->search ?? '';
        $sortColumn = $request->sort_column ?? '';
        $sortDirection = $request->sort_direction ?? '';

        try {
            $query = ServiceMasterData::
                // Select the required columns
                select(
                    'service_master_data.*',
                    'm.item_code',
                    'm.description',
                    'i.name',
                    DB::raw('CONCAT(u.first_name, " ", u.last_name) as added_by'),
                    DB::raw('CONCAT(user.first_name, " ", user.last_name) as initially_installed'),
                )
                // Join with equipment (master_data)
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'service_master_data.master_data_id', '=', 'm.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.mt_bp_institutions as i', 'service_master_data.institution', '=', 'i.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'service_master_data.added_by', '=', 'u.id')
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as user', 'service_master_data.initially_installed', '=', 'user.id');



            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }

            if ($request->has('status')) {
                $query->where('service_master_data.status', $request->status);
            }
            if ($request->has('statusInStock')) {
                $query->where('service_master_data.status', 'LIKE', '%' . $request->statusInStock . '%');
            }
            if ($request->has('category') && $request->category === 'pullout') {
                $query->where('service_master_data.status', 'Active')
                    ->where('service_master_data.institution', $request->item_in_institution_id);
            }
            $get_md_data = $query->orderBy($sortColumn, $sortDirection)->paginate(
                $pageSize,
                ['*'],
                'page',
                $current_page
            );

            return response()->json([
                'md_data' => $get_md_data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Get Master Data By Row ID */
    public function viewMasterDataByRowID(Request $request)
    {
        $id = $request->id;
        $data = ServiceMasterData::with(['institution_data' => function ($q) {
                $q->select('id', 'name', 'address')->first();
            }])
            ->with(['users_data'])
            ->with(['suppliers_data'])
            ->with(['master_data'])
            ->find($id);

        return response()->json([
            'data' => $data,
        ]);
    }







    /** ==================== CREATE MASTER DATA ================================ */
    public function createMasterData(Request $request)
    {
        $user_id = Auth::user()->id;
        $data = [
            'master_data_id' => $request->item_id,
            'equipment' => $request->equipment,
            'serial' => $request->serial,
            'sbu' => $request->sbu,
            'dealer_name' => $request->dealer_name,
            'area' => $request->area,
            'operation_time' => $request->operation_time,
            'software_version' => $request->software_version,
            'admission_date' => $request->admission_date,
            'date_installed' => $request->date_installed,
            'contract_due_date' => $request->contract_due_date,
            'region' => $request->region,
            'frequency' => $request->frequency,
            'analyzer_type' => $request->analyzer_type,
            'class' => $request->class,
            'initially_installed' => $request->initially_installed['user_id'] ?? null,
            'reason_equipment_status' => $request->reason_equipment_status,
            'email' => $request->email,
            'institution' => $request->institution['institutionId'] ?? 0,
            'mode' => $request->mode,
            'supplier' => $request->supplier['value'],
            'status' => $request->status,
            'added_by' => $user_id,
        ];

        try {
            DB::beginTransaction();

            $create = ServiceMasterData::create($data);
            if (!$create) {
                return response()->json(['error' => 'Unsuccessful']);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()]);
        }
    }




    /** ==================== EDIT MASTER DATA ================================ */
    public function editMasterData(Request $request)
    {
        $user_id = Auth::user()->id;
        $id = $request->id;
        try {
            DB::beginTransaction();

            $data = [
                'equipment' => $request->equipment,
                'serial' => $request->serial,
                'sbu' => $request->sbu,
                'dealer_name' => $request->dealer_name,
                'area' => $request->area,
                'operation_time' => $request->operation_time,
                'software_version' => $request->software_version,
                'admission_date' => $request->admission_date,
                'date_installed' => $request->date_installed,
                'contract_due_date' => $request->contract_due_date,
                'region' => $request->region,
                'frequency' => $request->frequency,
                'analyzer_type' => $request->analyzer_type,
                'class' => $request->class,
                'initially_installed' => $request->initially_installed ?? null,
                'reason_equipment_status' => $request->reason_equipment_status,
                'email' => $request->email,
                'institution' => $request->institution,
                'mode' => $request->mode,
                'supplier' => $request->supplier,
                'status' => $request->status,
                'added_by' => $user_id,
            ];


            $query = ServiceMasterData::find($id);
            if (!$query) {
                throw new Exception('Data not found');
            }
            $updated = $query->update($data);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()]);
        }
    }





    /** +========================== DELETE MASTER DATA ============================ */

    public function delete_master_data(Request $request)
    {
        $id = $request->id;

        try {
            DB::beginTransaction();

            $q = ServiceMasterData::find($id);
            $soft_delete = $q->delete();

            if (!$soft_delete) {
                throw new Exception('Unsuccessful');
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
}
