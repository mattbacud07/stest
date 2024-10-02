<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMLogs extends Model
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
