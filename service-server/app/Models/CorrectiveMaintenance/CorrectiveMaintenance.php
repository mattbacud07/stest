<?php

namespace App\Models\CorrectiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveMaintenance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'corrective_maintenance';
    const model_name = 'Corrective Maintenance';
    public $timestamps = true;


    protected $fillable = [
        'equipment_peripheral_id',
        'service_id',
        'item_id',
        'requested_by',
        'delegated_by',
        'delegated_to',
        'institution',
        'date_installed',
        'scheduled_at',
        'next_at',
        'status',
        'tag',
        'deleted_at',
    ];
}
