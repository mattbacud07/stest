<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalConfigModel;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdminApprovalConfig extends Controller
{
    public function get_logs(Request $request)
    {
         /** Search Column */
         $columnMappings = [
            'model' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'model'],
            'model_name' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'model_name'],
            'model_id' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'model_id'],
            'action' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'action'],
            'changes' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'changes'],
            'original' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'original'],
            'created_at' => ['table' => 'logs', 'db' => 'mysql', 'column' => 'created_at'],
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
            $query = Logs::
                select(
                    'logs.*',
                    DB::raw('CONCAT(u.first_name, " ", u.last_name) as user'),
                )
                ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'logs.user_id', '=', 'u.id');



            /** Server mode Details */
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $columnMappings) {
                    foreach ($columnMappings as $field => $mapping) {
                        $q->orWhere(DB::raw($mapping['table'] . '.' . $mapping['column']), 'like', "%{$search}%");
                    }
                });
            }

            $get_logs = $query->orderBy($sortColumn, $sortDirection)->paginate(
                $pageSize,
                ['*'],
                'page',
                $current_page
            );
            $models = collect(File::allFiles(app_path('Models')))
            ->map(fn ($file) => 'App\\Models\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->values();
            return response()->json([
                'logs' => $get_logs,
                'models' => $models,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
