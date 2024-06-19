<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMSetting extends Model
{
    use HasFactory;

    protected $table='pm_setting';

    protected $fillable = [
        'equipment',
        'schedule',
        'created_at',
        'updated_at'
    ];
}
