<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngineerActivities extends LogsBaseModel
{
    use HasFactory;


    protected $table = 'engineer_activities';
    const model_name = 'Engineer Activities';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'status',
        'acted_at',
        'type',
    ];


    /** Statusses */

    public const InTransit = 'InTransit';
    public const Started = 'Started';
    public const Ended = 'Ended';

    
}
