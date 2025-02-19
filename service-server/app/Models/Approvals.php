<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvals extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'approvals';
    const model_name = 'Approval Logs';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'user_id',
        'level',
        'status',
        'type',
        'remarks',
        'acted_at',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
