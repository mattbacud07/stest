<?php

namespace App\Models\WorkOrder;

use App\Models\LogsBaseModel;
use App\Models\MasterData;
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
        'request_type',
        'status',
        'created_at',
        'updated_at'
    ];


    public function master_data(){
        return $this->belongsTo(ServiceMasterData::class, 'service_master_data_id', 'id')
        ->select('id', 'sbu', 'equipment','serial');
    }
    
    public function general_master_data(){
        return $this->belongsTo(MasterData::class, 'item_id', 'id')
        ->select('id', 'item_code', 'description');
    }
}
