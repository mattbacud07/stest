<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Approvals extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'approvals';
    const model_name = 'Approval Logs';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'user_id',
        'level',
        'status',
        'type',
        'remarks',
        'acted_at',
    ];

    public function users()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id')
            ->select(
                'id',
                'first_name',
                'last_name',
                DB::raw(("CONCAT(first_name,' ',last_name) as full_name")),
                'department'
            );
    }


    public function approvers()
    {
        return $this->hasMany(ApprovalConfigModel::class, 'approval_level', 'level')
            ->where('approver_category', 1)
            ->join('role_user', 'role_user.id', '=', 'approval_configuration.role_user_id')
            ->select([
                'approval_configuration.user_id',
                'approval_configuration.approval_level',
                'approval_configuration.role_user_id',
                'role_user.id',
                'role_user.sbu',
                'role_user.satellite',
            ]);
    }
}
