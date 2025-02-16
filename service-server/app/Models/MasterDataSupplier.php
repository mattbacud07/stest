<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataSupplier extends Model
{
    use HasFactory;

    
    protected $connection = 'mysqlSecond';

    protected $table = 'mt_bp_suppliers';

    protected $fillable = [
        'bp_code',
        'name',
    ];
}
