<?php

namespace App\Models\authLogin;

use App\Models\RoleUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    
    protected $connection = 'mysqlSecond';

    protected $table = 'users';
    public $timestamps = true;
    protected $fillable = ['email', 'password', 'first_name', 'last_name'];


    public function roleUsers(){
        return $this->hasMany(RoleUser::class, 'user_id', 'id');
    }


    // Get fullname
    public function getFullName(){
        return "{$this->first_name} {$this->last_name}";
    }

        /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function createToken($name, array $abilities = ['*'])
    // {
    //     return $this->tokens()->create([
    //         'name' => $name,
    //         'token' => hash('sha256', $plainTextToken = Str::random(40)),
    //         'abilities' => $abilities,
    //     ]);
    // }
}
