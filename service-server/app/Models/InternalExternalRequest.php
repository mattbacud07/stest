<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalExternalRequest extends Model
{
    use HasFactory;

    protected $table = 'internal_external_requests';

    protected $fillable = ['name'];
}
