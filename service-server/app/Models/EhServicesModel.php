<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    
}
