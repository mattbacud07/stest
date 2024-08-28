<?php

namespace App\Models\PreventiveMaintenance;

use App\Models\Admin\PMSetting;
use App\Models\authLogin\UserModel;
use App\Models\EhServicesModel;
use App\Models\MasterData;
use App\Models\MasterDataInstitution;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Models\WorksDone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;


    protected $table = 'preventive_maintenance';
    public $timestamps = true;


    protected $fillable = [
        'equipment_peripheral_id',
        'service_id',
        'item_id',
        'date_installed',
        'scheduled_at',
        'list_scheduled',
        'next_at',
        'customer_complaint',
        'date_received',
        'work_type',
        'cs_actions',
        'engineer',
        'departed_date',
        'start_date',
        'end_date',
        'travel_duration',
        'work_duration',
        'actions_done_id',
        'remarks',
        'status',
        'status_after_service',
    ];



    /** Status */
    public const Scheduled = 'Scheduled';
    public const NotSet = 'Not Set';
    public const Delegated = 'Delegated';
    // public const PendingAcceptance = 'Pending Acceptance';
    public const Accepted = 'Accepted';
    public const InTransit = 'In Transit';
    public const InProgress = 'In Progress';
    // public const Backlog = 'Backlog';
    // public const Backjob = 'Backjob';
    public const Completed = 'Completed';
    public const Closed = 'Closed';


    public function equipment(){
        return $this->hasOne(MasterData::class, 'id', 'item_id');
    }
    public function frequency(){
        return $this->hasOne(PM_Setting::class, 'equipment', 'item_id');
    }

    public function eh(){
        return $this->hasOne(EhServicesModel::class, 'id', 'service_id');
    }

    public function equipment_peripherals(){
        return $this->hasOne(EquipmentPeripherals::class, 'id','equipment_peripheral_id');
    }

    public function user(){
        return $this->hasOne(UserModel::class, 'id', 'engineer');
    }
    
    
    public function actions(){
        return $this->hasMany(WorksDone::class, 'pm_id', 'id');
    }
}
