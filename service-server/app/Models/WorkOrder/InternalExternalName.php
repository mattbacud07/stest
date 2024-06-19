<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkOrder\InternalRequest;

class InternalExternalName extends Model
{
    use HasFactory;


    protected $table = 'internal_external_requests';

    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];

    public function internal_servicing_request(){
        return $this->belongsTo(InternalRequest::class, 'type_of_activity', 'id');
    }
}
