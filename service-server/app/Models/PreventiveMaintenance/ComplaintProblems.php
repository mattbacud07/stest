<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintProblems extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'complaint_problem';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'text',
        'type',
        'work_type',
    ];


    /** Actions Type */
    public const complaint = 'complaint';
    public const problem = 'problem';
}
