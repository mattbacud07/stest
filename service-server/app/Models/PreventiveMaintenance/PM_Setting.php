<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PM_Setting extends LogsBaseModel
{
    use HasFactory;


    protected $table = 'pm_setting';
    public $timestamps = true;
    protected $fillable = [
        'equipment',
        'schedule',
        'created_at',
        'updated_at'
    ];
}
