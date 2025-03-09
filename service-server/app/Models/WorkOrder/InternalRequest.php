<?php

namespace App\Models\WorkOrder;

use App\Models\authLogin\UserModel;
use App\Models\ChecklistItemAcquired;
use App\Models\EhServicesModel;
use App\Models\EngineerTaskDelegation;
use App\Models\LogsBaseModel;
use App\Models\WorkOrder\InternalExternalName;
use App\Models\WorksDone;
use App\Traits\GlobalVariables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InternalRequest extends LogsBaseModel
{
    use HasFactory;
    use GlobalVariables;

    protected $table = 'internal_request';
    const model_name = 'Internal Servicing Request';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'current_assigned_to',
        'current_assigned_by',
        'remarks',
        'option_type',
        'status',
        'created_at',
        'updated_at'
    ];

    public const OPTION_TYPE_STORAGE = 'storage';
    public const OPTION_TYPE_INSTALLATION = 'installation';
    public const OPTION_TYPE_UNINSTALLATION = 'uninstallation';

    public function equipment_handling()
    {
        return $this->belongsTo(EhServicesModel::class, 'service_id', 'id')->select(
            'id',
            'requested_by',
            'institution',
            'proposed_delivery_date'
        );
    }

    public function task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id', 'id')
            ->where('type', self::IS)
            ->where('active', 1)
            ->select('engineer_task_delegation.*', 'u.first_name', 'u.last_name')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'engineer_task_delegation.assigned_to', '=', 'u.id');
    }

    public function task_delegation_all()
    {
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')->where('type', self::IS);
    }

    public function internal_external_name()
    {
        return $this->hasOne(InternalExternalName::class, 'type_of_activity', 'id');
    }

    public function getUser()
    {
        return $this->hasOne(UserModel::class, 'delegated_to', 'id');
    }

    public function actions_done()
    {
        return $this->hasMany(WorksDone::class, 'work_type_id', 'id');
    }

    public function equipments()
    {
        return $this->hasMany(EquipmentPeripherals::class, 'service_id', 'service_id');
    }



}
