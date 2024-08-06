<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMSchedule extends Model
{
    use HasFactory;


    protected $table = 'pm_schedule';
    public $timestamps = true;

    protected $fillable = [
        'pm_id',
        'user_id',
        'frequency',
        'next_at',
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
        'created_at',
        'updated_at'
    ];
}
