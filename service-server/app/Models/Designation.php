<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;


    protected $table = 'approver_designation';

    public $timestamps = true;

    protected $fillable = [
        'approver_level',
        'approver_name',
        'approver_category'
    ];
}
