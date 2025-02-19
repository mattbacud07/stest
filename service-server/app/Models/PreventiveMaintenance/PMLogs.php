<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMLogs extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'pm_logs';
    public $timestamps = true;
    protected $fillable = [
        'service_id',
        'engineer',
        'remarks',
        'created_at',
        'updated_at'
    ];
}
