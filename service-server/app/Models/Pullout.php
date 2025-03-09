<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Traits\GlobalVariables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pullout extends LogsBaseModel
{
    use HasFactory;
    use GlobalVariables;


    protected $table = 'pullout';
    const model_name = 'Pullout Request';

    public $timestamps = true;

    protected $fillable = [
        'institution',
        'requested_by',
        'proposed_pullout_date',
        'final_schedule',
        'driver',
        'level',
        'status'
    ];




    public const SUPERVISOR = 1;
    public const OPERATION_SERVICE = 2;
    public const OPERATION_SERVICE_DELEGATION = 3;

    public const INSTALLATION_TL = 9;
    public const INSTALLATION_ENGINEER = 10;

    public const COMPLETE_LEVEL = 20;




    public const approver_category = 2;



    public const operations_dept = 6;
    public const service_dept = 9;



    //Status
    public const pending='Pending';
    public const disapproved='Disapproved';
    public const completed='Completed';



    public function pullout_decision_outbound(){
        return $this->hasMany(PulloutDecisionLog::class, 'service_id', 'id')
        ->where('status', self::PENDING)
        ->where('type', 'outbound');
    }
    public function pullout_decision_service(){
        return $this->hasMany(PulloutDecisionLog::class, 'service_id', 'id')
        ->where('status', self::PENDING)
        ->where('type', 'service');
    }
    public function counts_pending_decision(){
        return PulloutDecisionLog::where('service_id', $this->id)
        ->where('status', self::PENDING)->count();
    }


    public function equipments(){
        return $this->hasMany(EquipmentPeripherals::class, 'service_id', 'id')
        ->where('category', 'Equipment')
        ->where('request_type', 'pullout');
    }

    public function roleUser()
    {
        return $this->belongsTo(RoleUser::class, 'requested_by', 'user_id');
    }
    

    
    
}
