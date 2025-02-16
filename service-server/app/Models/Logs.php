<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;

    protected $fillable = ['model', 'model_id', 'action', 'changes', 'original', 'user_id'];

    public $timestamps = true;

    protected $casts = [
        'changes' => 'array', // Convert JSON to array automatically
        'original' => 'array',
    ];
}
