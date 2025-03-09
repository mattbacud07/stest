<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalConfigModel extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'approval_configuration';
    const model_name = 'Approval Configuration';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'roleID',
        'role_user_id',
        'approval_level',
        'approval_level_name',
        'approver_category',
        // 'sbu',
        // 'satellite'
    ];


    public function users()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id')
            ->select(
                'id',
                'first_name',
                'last_name',
                'department',
                DB::raw(("CONCAT(first_name,' ',last_name) as full_name"))
            );
    }
}
