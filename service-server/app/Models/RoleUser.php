<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;

    protected $table = 'role_user';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'role_id',
        // 'role_name',
        // 'description',
        'SSU',
    ];

    public function roles(){
        return $this->belongsTo(Roles::class, 'role_id','id');
    }

    public function users(){
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}
