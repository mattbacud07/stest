<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataInstitution extends Model
{
    use HasFactory;

    protected $connection = 'mysqlSecond';

    protected $table = 'mt_bp_institutions';

    protected $fillable = [
        'bp_code',
        'name',
        'address',
        'city',
        'state_province',
        'zip_postal_code',
        'area'
    ];
}
