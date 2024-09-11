<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;


    protected $table = 'roles';

    public $timestamps = true;

    protected $fillable = [
        'roleID',
        'role_name',
        'description',
        'permissions'
    ];

    /** Roles */
    public const adminRole = 'Administrator';
    public const approverRole = 'Approver';
    public const TLRole = 'Team Leader';
    public const engineerRole = 'Engineer';
    public const OutboundRole = 'Outbound Personnel';
    public const WIMRole = 'WIM Personnel';
}
