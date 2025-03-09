<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItemAcquired extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'checklist_item_acquired';
    const model_name = 'Checklist Item Acquired';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'user_id',
        'qty',
        'item',
        'remarks',
        'work_type'
    ];
}
