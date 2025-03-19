<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PulloutDecisionLog extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'pullout_decision_log';
    const model_name = 'Pullout Decision Log';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'assigned_by',
        'engineer',
        'driver',
        'scheduled_date',
        'preferred_schedule',
        'remarks',
        'status',
        'type',
    ];
}
