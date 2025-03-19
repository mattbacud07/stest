<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\LogsBaseModel;
use App\Models\MasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMPartsUsed extends LogsBaseModel
{
    use HasFactory;

    protected $table = 'parts_used';
    public $timestamps = true;

    protected $fillable = [
        'service_id',
        'item_id',
        'qty',
        'dr',
        'si',
        'remarks',
        'type'
    ];



    public function equipment(){
        return $this->belongsTo(MasterData::class, 'item_id','id')
        ->select('id','item_code','description');
    }
}
