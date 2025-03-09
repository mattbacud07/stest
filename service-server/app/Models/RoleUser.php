<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleUser extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'role_user';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'role_id',
        'supervisor_id',
        'sbu',
        'satellite',
    ];

    public function roles(){
        return $this->belongsTo(Roles::class, 'role_id','roleID');
    }

    public function users(){
        return $this->belongsTo(UserModel::class, 'user_id', 'id')
        ->select('id','first_name','last_name','department');
    }


    public function supervisor(){
        return $this->belongsTo(UserModel::class, 'supervisor_id', 'id')
        ->select('id','first_name','last_name',
        DB::raw('CONCAT(first_name, " ", last_name) as supervisor_name'));
    }

    public function approver(){
        return $this->hasMany(ApprovalConfigModel::class, 'role_user_id', 'id');
    }

}
