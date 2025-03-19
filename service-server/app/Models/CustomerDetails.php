<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDetails extends Model
{
    use HasFactory;

    protected $table='customer_details';

    public $timestamps= true;

    protected $fillable = [
        'service_id',
        'name',
        'designation',
        'signature',
        'remarks',
        'type'
    ];
}
