<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PulloutDecisionLog extends Model
{
    use HasFactory;

    protected $table = 'pullout_decision_log';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'assigned_by',
        'scheduled_date',
        'preferred_schedule',
        'remarks',
        'status',
        'type',
    ];
}
