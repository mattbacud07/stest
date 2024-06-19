<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkOrder\InternalRequest;

class EhServicesModel extends Model
{
    use HasFactory;

    protected $table = 'equipment_handling';

    protected $fillable = [
        'report_number',
        'requested_by',
        'institution',
        'address',
        'proposed_delivery_date',
        'other_request_details',
        'external_request',
        'internal_request',
        'endorsement',
        'additional_remarks'
    ];

    public function equipments(){
        return $this->hasMany('equipment');
    }

    public function internal_servicing(){
        return $this->belongsTo(InternalRequest::class, 'service_id', 'id');
    }
    public function internal_servicing_request(){
        return $this->hasOne(InternalRequest::class, 'service_id', 'id');
    }
    
}
