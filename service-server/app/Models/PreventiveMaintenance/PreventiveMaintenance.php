<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;


    protected $table = 'preventive_maintenance';
    public $timestamps = true;


    protected $fillable = [
        'service_id',
        'scheduled_at',
        'list_scheduled',
        'user_id',
        'item_id',
        'institution',
        'serial_number',
        'status',
        'created_at',
        'updated_at'
    ];

    /** Status */
    public const Scheduled = 'Scheduled';
    public const NotSet = 'Not Set';
    public const PendingAcceptance = 'Pending Acceptance';
    public const Accepted = 'Accepted';
    public const InProgress = 'In Progress';
    public const Backlog = 'Backlog';
    public const Backjob = 'Backjob';
    public const Completed = 'Completed';
    public const Closed = 'Closed';
}
