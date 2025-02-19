<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMActions extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'pm_actions';

    public $timestamps = true;

    protected $fillable = [
        'maintenance_id',
        'actions',
        'type',
        'work_type',
    ];


    /** Actions Type */
    public const complaint = 'Complaint';
    public const problem = 'Problem';
    public const actions_taken = 'ActionsTaken';
    public const Remarks = 'Remarks';
}
