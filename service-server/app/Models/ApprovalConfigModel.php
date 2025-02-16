<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalConfigModel extends Model
{
    use HasFactory;

    protected $table = 'approval_configuration';

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


    // public function role_user(){
    //     return $this->belongsTo(RoleUser::class, 'role_user_id', 'id');
    // }
}
