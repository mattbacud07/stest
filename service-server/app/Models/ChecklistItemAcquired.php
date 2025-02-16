<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItemAcquired extends Model
{
    use HasFactory;

    protected $table = 'checklist_item_acquired';

    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'user_id',
        'qty',
        'item',
        'remarks',
    ];
}
