<?php

namespace App\Models\PreventiveMaintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMOptionsAction extends Model
{
    use HasFactory;

    protected $table='pm_options_action';

    public $timestamps = true;

    protected $fillable = [ 'actions', ];
}
