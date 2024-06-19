<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;


protected $table = 'preventive_maintenance';

protected $fillable = [
    'user_id',
    'institution',
    'serial_number',
    'equipment_model',
    'customer_complaint',
    'date_received',
    'work_type',
    'cs_actions',
    'engineer',
    'departed_date',
    'start_date',
    'actions_done',
    'end_date',
    'travel_duration',
    'work_duration',
    'remarks',
    'software_version',
    'status',
];

}
