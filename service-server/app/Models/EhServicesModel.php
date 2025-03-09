<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use App\Models\PreventiveMaintenance\PMPartsUsed;
use App\Models\WorkOrder\EquipmentPeripherals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkOrder\InternalRequest;
use App\Services\TaskDelegationService;
use App\Traits\GlobalVariables;
use Illuminate\Support\Facades\DB;

class EhServicesModel extends LogsBaseModel
{
    use HasFactory;

    const model_name = 'Equipment Handling';

    protected $table = 'equipment_handling';
    public $timestamps = true;
    protected $fillable = [
        'report_number',
        'requested_by',
        'institution',
        'satellite',
        'proposed_delivery_date',
        'with_contract',
        'attach_gate',
        'ship',
        'bypass',
        'occular',
        'other_request_details',
        'request_type',
        'other',
        'endorsement',
        'additional_remarks',
        'final_installation_date',
        'driver',
        'tl_assigned',
        'SBU',
        'assigned_date',
        'receiving_option',
        'installer',
        'date_installed',
        'level',
        'main_status',
        'created_at',
        'updated_at'
    ];



    /**
     * Declared public variables for Equipment Handling Signatories - Job Order Form.
     * S -> Satellite Level of Offices
     */
    public const IT_DEPARTMENT = 1;
    public const SM_SER = 2;
    public const WIM = 3;
    public const SERVICE = 4;
    public const BILLING_WIM = 5;
    public const OUTBOUND = 6;
    public const S_IT_DEPARTMENT = 7;
    public const S_SM_SER = 8;
    public const S_WIM = 9;
    // public const S_SERVICE = 10;
    public const S_OUTBOUND = 10;
    public const S_BILLING_WIM = 11;

    public const INSTALLATION_TL = 18;
    public const INSTALLATION_ENGINEER = 19;
    public const EH_SIGNATORY_COMPLETE = 20;


    /** 
     * Work Order Status
     */
    public const ONGOING = 1;
    public const DISAPPROVED = 2;
    public const INTERNAL_SERVICING = 3;
    public const INSTALLING = 4;
    public const COMPLETE = 5;
    public const FOR_STORAGE = 6;

    /** External Request Option */
    public const REQUEST_TYPE = 4; //Shipment/Delivery


    /**
     * Approver Category
     */
    public const EH_INSTALLATION = 1;
    public const EH_PULLOUT = 2;



    /**
     * Satellite Offices
     */
    public const makatiWarehouse = 'Makati-Warehouse';
    public const cdo = 'CDO';
    public const davao = 'Davao';
    public const zamboanga = 'Zamboanga';
    public const iloilo = 'Iloilo';
    public const cebu = 'Cebu';
    public const tacloban = 'Tacloban';
    public const dumaguete = 'Dumaguete';




    /**
     * Receiving Options
     */
    public const door_to_door = "door";
    public const pickup = "pickup";


    /** Departments */
    public const sm_department = 8; //Sales & Marketing Dept.
    public const service_department = 9; // Service Department
    public const operation_department = 6; // Operation Dept.





    // public function equipments()
    // {
    //     return $this->hasMany('equipment');
    // }

    public function equipments()
    {
        return $this->hasMany(EquipmentPeripherals::class, 'service_id', 'id')
        ->where('category', 'Equipment')
        ->where('request_type', 'eh');
    }
    public function peripherals()
    {
        return $this->hasMany(EquipmentPeripherals::class, 'service_id', 'id')
        ->where('category', 'Peripheral')
        ->where('request_type', 'eh');
    }

    public function institution(){
        return $this->belongsTo(MasterDataInstitution::class, 'institution', 'id')
        ->select('id','name','address');
    }

    public function users(){
        return $this->belongsTo(UserModel::class, 'requested_by', 'id')
        ->select('id','first_name','last_name','department',
        DB::raw(("CONCAT(first_name,' ',last_name) as full_name")));
    }

    public function internal_servicing_request()
    {
        return $this->hasOne(InternalRequest::class, 'service_id', 'id');
    }


    /** Task Delegation Section  */
    public function task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id', 'id')
            ->where('type', 'eh')
            ->where('active', 1)
            ->select('engineer_task_delegation.*', 'u.first_name', 'u.last_name')
            ->leftjoin(DB::connection('mysqlSecond')->getDatabaseName() . '.users as u', 'engineer_task_delegation.assigned_to', '=', 'u.id');
    }

    public function task_delegation_all()
    {
        return $this->hasMany(EngineerTaskDelegation::class, 'service_id', 'id')->where('type', 'eh');
    }

    public function latest_task_delegation()
    {
        return $this->hasOne(EngineerTaskDelegation::class, 'service_id','id')
            ->latest(); // Orders by created_at DESC by default
    }
    
    
    public function approval_logs()
    {
        return $this->hasMany(Approvals::class, 'service_id','id')
            ->select('id','service_id','user_id', 'level','status', 'remarks', 'acted_at')
            ->where('type', 'eh'); // Approval Logs
    }

}
