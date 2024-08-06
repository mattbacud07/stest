<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = "permission";

    public $timestamps = true;

    protected $fillable = [
        'role_id',
        'access_type',
        'access_module',
        'status'
    ];

    public const defaultAccessType = [
        // 'createRequest',
        'viewRequest',
        'viewMainRequest',
        'approveRequest',
        'equipmentHandlingRequest',
        'internalServicingRequest',
    ];

    public function roles()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'id');
    }
}
