<?php

namespace App\Models\CorrectiveMaintenance;

use App\Models\EngineerTaskDelegation;
use App\Models\MasterDataInstitution;
use App\Models\ServiceMasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CorrectiveMaintenance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'corrective_maintenance';
    const model_name = 'Corrective Maintenance';
    public $timestamps = true;


    protected $fillable = [
        'equipment_peripheral_id',
        'service_id',
        'item_id',
        'requested_by',
        'delegated_by',
        'delegated_to',
        'institution',
        'date_installed',
        'scheduled_at',
        'status',
        'deleted_at',
    ];


    public function institution()
    {
        return $this->belongsTo(MasterDataInstitution::class, 'institution', 'id')
            ->select('id', 'name', 'address');
    }
    public function equipment()
    {
        return $this->belongsTo(ServiceMasterData::class, 'item_id', 'id')
            ->select('id', 'equipment', 'serial', 'frequency', 'sbu');
    }


    /** Task Delegation Section  */
    public function task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id', 'id')
            ->where('engineer_task_delegation.type', 'cm')
            ->where('engineer_task_delegation.active', 1)
            ->select('engineer_task_delegation.*', 'u.first_name', 'u.last_name','i.path')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'engineer_task_delegation.assigned_to', '=', 'u.id')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.images as i', 'u.id', '=', 'i.user_id')
            ->where('i.type','signature');
    }

    public function task_delegation_all()
    {
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')->where('type', 'cm');
    }

    public function latest_task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id', 'id')
            ->latest(); // Orders by created_at DESC by default
    }
}
