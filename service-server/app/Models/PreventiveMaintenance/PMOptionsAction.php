<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMOptionsAction extends LogsBaseModel
{
    use HasFactory;

    protected $table='pm_options_action';

    public $timestamps = true;

    protected $fillable = [ 'actions', ];
}
