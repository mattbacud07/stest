<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\Admin\PMSetting;
use App\Models\authLogin\UserModel;
use App\Models\EhServicesModel;
use App\Models\EngineerTaskDelegation;
use App\Models\LogsBaseModel;
use App\Models\MasterData;
use App\Models\MasterDataInstitution;
use App\Models\ServiceMasterData;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Models\WorkOrder\InternalRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenance extends LogsBaseModel
{
    use HasFactory;
    use SoftDeletes;


    protected $table = 'preventive_maintenance';
    const model_name = 'Preventive Maintenance';
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
        'next_at',
        'status',
        'tag',
        'deleted_at',
    ];

    /** Status */
    public const Scheduled = 'Scheduled';
    public const ReadyForDelegation = 'Ready for Delegation';
    public const NotSet = 'Not Set';
    public const Delegated = 'Delegated';
    public const Accepted = 'Accepted';
    public const InTransit = 'In Transit';
    public const InProgress = 'In Progress';
    public const Completed = 'Completed';
    public const Closed = 'Closed';

    /** PM Tag */
    public const pm_tag_under_observation = 'Under Observation';
    public const pm_tag_set_observation = 'Set Observation Period';
    public const pm_tag_backlog = 'BackLog';
    public const pm_tag_backjob = 'BackJob';
    public const pm_tag_non_operational = 'Non-operational';
    public const pm_tag_completed = 'Completed';


    /** Status after Service */
    public const operational = 'Operational';
    public const further_monitoring = 'Further Monitoring';
    public const non_operational = 'Non-Operational';

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
            ->where('type', 'pm')
            ->where('active', 1)
            ->select('engineer_task_delegation.*', 'u.first_name', 'u.last_name')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'engineer_task_delegation.assigned_to', '=', 'u.id');
    }

    public function task_delegation_all()
    {
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')->where('type', 'pm');
    }

    public function latest_task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id', 'id')
            ->latest(); // Orders by created_at DESC by default
    }
}
