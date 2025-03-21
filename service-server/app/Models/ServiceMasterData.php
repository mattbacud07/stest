<?php

namespace App\Models;

use App\Models\authLogin\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    // public function institution(){
    //     return $this->belongsTo(MasterDataInstitution::class, 'institution', 'id')
    //     ->select('id','name','address');
    // }

    // public function users(){
    //     return $this->belongsTo(UserModel::class, 'requested_by', 'id')
    //     ->select('id','first_name','last_name','department',
    //     DB::raw(("CONCAT(first_name,' ',last_name) as full_name")));
    // }

    public function institution_data(){
        return $this->belongsTo(MasterDataInstitution::class, 'institution', 'id');
    }
    public function users_data(){
        return $this->belongsTo(UserModel::class, 'requested_by', 'id')
        ->select('id','first_name','last_name','department',
        DB::raw(("CONCAT(first_name,' ',last_name) as full_name")));
    }

    public function suppliers_data(){
        return $this->belongsTo(MasterDataSupplier::class, 'supplier','id')
        ->select('id','bp_code', 'name');
    }
    public function master_data(){
        return $this->belongsTo(MasterData::class, 'master_data_id', 'id')
        ->select('id','item_code','description');
    }
}
