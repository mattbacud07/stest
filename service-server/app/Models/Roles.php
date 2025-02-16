<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
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

    // protected $casts = [
    //     'permissions' => 'array',
    // ];


    /** Roles */
    public const adminRole = 'Administrator';
    public const approverRole = 'Approver';
    public const TLRole = 'Team Leader';
    public const engineerRole = 'Engineer';
    public const OutboundRole = 'Outbound Personnel';
    public const WIMRole = 'WIM Personnel';

    /** ROLE ID */
    public const adminRoleID = 6;
    public const approverRoleID = 1;
    public const TLRoleID = 2;
    public const engineerRoleID = 3;
    public const OutboundRoleID = 4;
    public const requestorID = 5;
}
