<?php

namespace App\Models\WorkOrder;

use App\Models\LogsBaseModel;
use App\Models\ServiceMasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentPeripherals extends LogsBaseModel
{
    use HasFactory;

    protected $table= 'equipment_peripherals';
    const model_name = 'Equipment Peripherals';
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


    public function master_data(){
        return $this->belongsTo(ServiceMasterData::class, 'service_master_data_id', 'id')
        ->select('id', 'sbu', 'equipment');
    }
}
