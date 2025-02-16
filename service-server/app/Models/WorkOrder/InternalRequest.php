<?php

namespace App\Models\WorkOrder;

use App\Models\authLogin\UserModel;
use App\Models\EhServicesModel;
use App\Models\EngineerTaskDelegation;
use App\Models\WorkOrder\InternalExternalName;
use App\Models\WorksDone;
use App\Traits\GlobalVariables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    use HasFactory;
    use GlobalVariables;

    protected $table = 'internal_request';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'current_assigned_to',
        'current_assigned_by',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    /** Relationship of created work order to Internal Request servicing */
    // public function equipment_handling(){
    //     return $this->belongsTo(EhServicesModel::class, 'service_id', 'id');
    // }
    
    public function equipment_handling(){
        return $this->belongsTo(EhServicesModel::class, 'service_id', 'id');
    }
    
    public function task_delegation(){
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')
                    ->where('type', self::IS)
                    ->where('active', 1);
    }

    public function task_delegation_all(){
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')
                    ->where('type', self::IS);
    }

    public function internal_external_name(){
        return $this->hasOne(InternalExternalName::class, 'type_of_activity','id');
    }
   
    public function getUser(){
        return $this->hasOne(UserModel::class, 'delegated_to','id');
    }

    public function actions_done(){
        return $this->hasMany(WorksDone::class, 'work_type_id', 'id');
    }

    public function equipments(){
        return $this->hasMany(EquipmentPeripherals::class, 'service_id', 'service_id');
    }


}


