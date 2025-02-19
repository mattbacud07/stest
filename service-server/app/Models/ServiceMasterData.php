<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceMasterData extends LogsBaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'service_master_data';
    const model_name = 'Service Master Data';

    public $timestamps = true;

    protected $fillable = [
        'master_data_id',
        'equipment',
        'serial',
        'sbu',
        'dealer_name',
        'area',
        'operation_time',
        'software_version',
        'admission_date',
        'date_installed',
        'contract_due_date',
        'region',
        'frequency',
        'analyzer_type',
        'class',
        'initially_installed',
        'reason_equipment_status',
        'email',
        'institution',
        'mode',
        'supplier',
        'status',
        'added_by',
        // 'deleted_at',
    ];


    /** Status */
    public const active = 'Active';
    public const inActive = 'InActive';
    public const inStock = 'In-stock';


    public function institution_data(){
        return $this->hasOne(MasterDataInstitution::class, 'id', 'institution');
    }
    public function users_data(){
        return $this->hasOne(UserModel::class, 'id','initially_installed')
        ->select('id','first_name', 'last_name');
    }
    public function suppliers_data(){
        return $this->hasOne(MasterDataSupplier::class, 'id','supplier')
        ->select('id','bp_code', 'name');
    }
    public function master_data(){
        return $this->hasOne(MasterData::class, 'id', 'master_data_id')
        ->select('id','item_code','description');
    }
}
