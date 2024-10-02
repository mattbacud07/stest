<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SSU extends Model
{
    use HasFactory;

    protected $table = 'ssu';

    public $timestamps = true;

    protected $fillable = [
        'ssu',
    ];
}
