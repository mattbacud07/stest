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
