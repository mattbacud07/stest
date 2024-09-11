<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\MasterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMPartsUsed extends Model
{
    use HasFactory;

    protected $table = 'pm_parts_used';
    public $timestamps = true;

    protected $fillable = [
        'item_id',
        'item_description',
        'qty',
        'dr',
        'si',
        'remarks',
        'created_at',
        'updated_at',
    ];



    public function equipment(){
        return $this->belongsTo(MasterData::class, 'item_id','id');
    }
}
