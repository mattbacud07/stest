<?php

namespace App\Services;

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
    // public function updateStatusGeneral($service_id, $tl_assigned, $assigned_date, $installer, $date_installed, $next_approver, $main_status)
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
        $levels = [
            self::IT_DEPARTMENT => [self::APM_NSM_SM, self::ONGOING],
            self::APM_NSM_SM => [self::WIM, self::ONGOING],
            self::WIM => [self::SERVICE_TL, self::ONGOING],
            self::SERVICE_TL => [self::SERVICE_HEAD_ENGINEER, self::ONGOING],
            self::SERVICE_HEAD_ENGINEER => [self::BILLING_WIM, self::ONGOING],
            self::BILLING_WIM => [self::INSTALLATION_TL, self::INSTALLING, $institution_area],
            // self::INSTALLATION_TL => [self::INSTALLATION_ENGINEER, self::INSTALLING],
            // self::INSTALLATION_ENGINEER => [self::INSTALLATION_ENGINEER + 1, self::COMPLETE]
        ];

        return $levels[$current_level] ?? throw new Exception('Invalid approver');
    }

    //End of the Class
}
