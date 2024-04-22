<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    use HasFactory;

    protected $table = 'internal_request';

    protected $fillable = [
        'service_id',
        'received_by',
        'type_of_activity',
        'other',
        'delegation_date',
        'delegated_to',
        'date_started',
        'accomplished_date',
        'packed_endorse_to_warehouse',
        'packed_date',
        'checklist_number',
        'endorsement_date',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];
}
