<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    use HasFactory;

    protected $table = 'module';

    public $timestamps = true;

    protected $fillable = [
        'role_id',
        'title',
        'access_module',
        'module_type',
        'status',
        'created_at',
        'updated_at'
    ];

    public const defaultModule = [
        ['title' => 'Equipment Handling', 'access_module' => 'EH', 'module_type' => 'EH'],
        ['title' => 'Internal Servicing', 'access_module' => 'IS', 'module_type' => 'EH'],
        ['title' => 'Preventive Maintenance', 'access_module' => 'PM', 'module_type' => 'PM'],
        ['title' => 'Corrective Maintenance', 'access_module' => 'CM', 'module_type' => 'CM'],
    ];

    public function roles(){
        return $this->belongsTo(Roles::class, 'role_id', 'roleID');
    }
}
