<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkOrder\InternalRequest;

class EhServicesModel extends Model
{
    use HasFactory;

    protected $table = 'equipment_handling';
    public $timestamps = true;
    protected $fillable = [
        'report_number',
        'requested_by',
        'institution',
        'address',
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
        'assigned_date',
        'installer',
        'date_installed',
        'status',
        'main_status',
        'created_at',
        'updated_at'
    ];


    
    /**
     * Declared public variables for Equipment Handling Signatories - Job Order Form.
     */
    public const IT_DEPARTMENT = 1;
    public const APM_NSM_SM = 2;
    public const WIM = 3;
    public const SERVICE_TL = 4;
    public const SERVICE_HEAD_ENGINEER = 5;
    public const BILLING_WIM = 6;
    public const OUTBOUND = 7;
    public const AREA_WIM = 8;
    public const AREA_RSM_SPM_SNM_SM = 9;
    public const AREA_SERVICE_TL = 10;
    public const AREA_BILLING_WIM = 11;

    public const INSTALLATION_TL = 18;
    public const INSTALLATION_ENGINEER = 19;
    public const EH_SIGNATORY_COMPLETE = 20;


    // public const TL = 8;
    // public const INSTALLATION_ENGINEER = 9;

    /** 
     * Work Order Status
     */
    public const ONGOING = 1;
    public const ONGOING_AREA_LEVEL = 2;
    public const COMPLETE = 3;
    public const DISAPPROVED = 4;
    public const RESCHEDULE = 5;
    public const INSTALLING = 6;


    public function equipments()
    {
        return $this->hasMany('equipment');
    }

    public function internal_servicing()
    {
        return $this->belongsTo(InternalRequest::class, 'service_id', 'id');
    }
    public function internal_servicing_request()
    {
        return $this->hasOne(InternalRequest::class, 'service_id', 'id');
    }
}
