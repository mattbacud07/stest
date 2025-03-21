<?php

namespace App\Models;

use App\Models\PreventiveMaintenance\PMPartsUsed;
use App\Traits\GlobalVariables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EngineerTaskDelegation extends LogsBaseModel
{
    use GlobalVariables;
    use HasFactory;

    protected $table = 'engineer_task_delegation';
    const model_name = 'Engineer Task Delegation';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'assigned_to',
        'assigned_by',
        'travel_duration',
        'option_type',
        'status_after_service',
        'complaint',
        'problem',
        'sr_remarks',
        'tag',
        'monitoring_end',
        'status',
        'active',
        'type',
        'remarks',
        'acted_at',
    ];

    public const STATUS_PENDING = 'Pending';
    public const STATUS_DECLINED = 'Declined';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_COMPLETED = 'Completed';

  



    public function task_activity(){
        return $this->hasMany(EngineerActivities::class, 'service_id', 'id');
    }


    public function actions_taken(){
        return $this->hasMany(WorksDone::class, 'service_id', 'id');
    }

    public function items_acquired()
    {
        return $this->hasMany(ChecklistItemAcquired::class, 'service_id', 'id');
    }

    public function spareparts()
    {
        return $this->hasMany(PMPartsUsed::class, 'service_id','id');
    }
    
    public function customer()
    {
        return $this->hasOne(CustomerDetails::class, 'service_id','id');
    }
}
