<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMDetails extends LogsBaseModel
{
    use HasFactory;


    protected $table = 'pm_details';
    public $timestamps = true;

    protected $fillable = [
        'pm_id',
        'user_id',
        'frequency',
        'next_at',
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
