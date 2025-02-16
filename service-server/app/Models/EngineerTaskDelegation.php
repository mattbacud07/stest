<?php

namespace App\Models;

use App\Traits\GlobalVariables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EngineerTaskDelegation extends Model
{
    use GlobalVariables;
    use HasFactory;

    protected $table = 'engineer_task_delegation';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'assigned_to',
        'assigned_by',
        'option_type',
        'status_after_service',
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
        return $this->hasMany(EngineerActivities::class, 'service_id', 'id')
                    ->where('type', self::IS);
    }


    public function actions_taken(){
        return $this->hasMany(WorksDone::class, 'service_id', 'id')
                    ->where('work_type', self::IS);
    }
}
