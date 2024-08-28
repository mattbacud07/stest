<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorksDone extends Model
{
    use HasFactory;

    protected $table = 'actions_done';

    public $timestamps = true;

    protected $fillable = [
        'work_type_id',
        'equipment_id',
        'service_id',
        'pm_id',
        'serial',
        'action',
        'work_type',
    ];

    public function InternalRequest(){
        return $this->belongsTo(InternalExternalRequest::class, 'work_type_id', 'id');
    }
}
