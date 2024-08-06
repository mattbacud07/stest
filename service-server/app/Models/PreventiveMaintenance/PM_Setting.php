<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PM_Setting extends Model
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
