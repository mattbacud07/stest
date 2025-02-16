<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngineerActivities extends Model
{
    use HasFactory;


    protected $table = 'engineer_activities';

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
