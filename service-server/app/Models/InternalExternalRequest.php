<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalExternalRequest extends Model
{
    use HasFactory;

    protected $table = 'internal_external_requests';
    public $timestamps = true;
    protected $fillable = ['name'];

    /** External Request for PM Generation */
    public const ReagentTieup = 2;
    public const Purchased = 3;
}
