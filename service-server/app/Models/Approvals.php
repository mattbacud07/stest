<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvals extends Model
{
    use HasFactory;

    protected $table = 'approvals';

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
