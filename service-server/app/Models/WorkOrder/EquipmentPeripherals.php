<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentPeripherals extends Model
{
    use HasFactory;

    protected $table= 'equipment_peripherals';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'item_id',
        'service_master_data_id',
        'item_code',
        'serial_number',
        'SBU',
        'remarks',
        'description',
        'category',
        'status',
        'created_at',
        'updated_at'
    ];
}
