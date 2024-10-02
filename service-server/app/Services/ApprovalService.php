<?php

namespace App\Services;

use App\Models\EhServicesModel as EH;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Traits\GlobalVariables;

class ApprovalService
{
    use GlobalVariables;

    /** Update Equipment Peripherals - IT Department Level - filled in Serial Numbers */
    public function updateEquipmentPeripherals($items)
    {
        foreach ($items as $item) {
            $update_item_peripheral = DB::table('equipment_peripherals')
                ->whereIn('id', [$item['id']]) // Wrap $item['id'] in an array
                ->update(['serial_number' => $item['serial']]);

            if (!$update_item_peripheral) {
                throw new Exception('IT Level - Failed to update equipment peripherals.');
            }
        }
    }

    /** Update status of Request from IT to APM */
    public function updateStatus($service_id, $status)
    {
        $updateStatus = DB::table('equipment_handling')
            ->where('id', $service_id)
            ->update([
                'status' => $status,
                'updated_at' => Carbon::now(),
            ]);
        if (!$updateStatus) {
            throw new Exception('IT level. Failed to update approver status');
        }
    }

    /** Update Status for General Query */
    public function updateStatusGeneral($service_id, $dataArray, $next_approver, $main_status)
    {
        $status = [
            'status' => $next_approver,
            'main_status' => $main_status,
        ];
        $updateStatus = DB::table('equipment_handling')
            ->where('id', $service_id)
            ->update(array_merge($dataArray, $status));

        if (!$updateStatus) {
            throw new Exception('Failed to update approver status');
        }
    }

    /** Log Approvals */
    public function logApproval($service_id, $user_id, $remark, $level, $main_status)
    {
        $logApproval = DB::table('approvals')->insert([
            'service_id' => $service_id,
            'user_id' => $user_id,
            'remarks' => $remark,
            'level' => $level,
            'status' => $main_status,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        if (!$logApproval) {
            throw new Exception('Failed to log approval details');
        }
    }

    /** Get Next Approval */
    public function getNextApprovalLevel($current_level, $institution_area)
    {
        $basedOnArea = $institution_area !== 'luzon' && $institution_area !== 'LUZON' && $institution_area !== 'Luzon'
         ? EH::AREA_WIM //if false
         : EH::INSTALLATION_TL; //if true
        $levels = [
            EH::IT_DEPARTMENT => [EH::APM_NSM_SM, EH::ONGOING],
            EH::APM_NSM_SM => [EH::WIM, EH::ONGOING],
            EH::WIM => [EH::SERVICE_TL, EH::ONGOING],
            EH::SERVICE_TL => [EH::SERVICE_HEAD_ENGINEER, EH::ONGOING],
            EH::SERVICE_HEAD_ENGINEER => [EH::BILLING_WIM, EH::ONGOING],
            EH::BILLING_WIM => [EH::OUTBOUND, EH::ONGOING],
            EH::OUTBOUND => [$basedOnArea, EH::ONGOING],
            EH::AREA_WIM => [EH::AREA_RSM_SPM_SNM_SM, EH::ONGOING],
            EH::AREA_RSM_SPM_SNM_SM => [EH::AREA_SERVICE_TL, EH::ONGOING],
            EH::AREA_SERVICE_TL => [EH::AREA_BILLING_WIM, EH::ONGOING],
            EH::AREA_BILLING_WIM => [EH::INSTALLATION_TL, EH::ONGOING],

            EH::INSTALLATION_TL => [EH::INSTALLATION_ENGINEER, EH::INSTALLING],
            EH::INSTALLATION_ENGINEER => [EH::INSTALLATION_ENGINEER + 1, EH::COMPLETE]
        ];

        return $levels[$current_level] ?? throw new Exception('Invalid approver');
    }

    //End of the Class
}
