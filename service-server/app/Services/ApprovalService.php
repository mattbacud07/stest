<?php

namespace App\Services;

use App\Models\Approvals;
use App\Models\EhServicesModel as EH;
use App\Models\WorkOrder\EquipmentPeripherals;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Traits\GlobalVariables;
use Illuminate\Support\Facades\Auth;

class ApprovalService
{
    use GlobalVariables;

    /** Update Equipment Peripherals - IT Department Level - filled in Serial Numbers */
    public function updateEquipmentPeripherals($items)
    {
        if (empty($items)) {
            throw new Exception('Empty array of equipment. Failed to update serial number');
        } else {
            foreach ($items as $item) {
                $update_item_peripheral = EquipmentPeripherals::whereIn('id', [$item['id']])
                    ->update(['serial_number' => $item['serial']]);


                if (!$update_item_peripheral) {
                    throw new Exception('IT Level - Failed to update equipment peripherals.');
                }
            }
        }
    }


    /** Update Status for General Query */
    public function updateStatusGeneral($service_id, $dataArray, $next_approver, $main_status)
    {
        $status = [
            'level' => $next_approver,
            'main_status' => $main_status,
        ];

        $query = EH::find($service_id);
        if (!$query) {
            throw new Exception('No records found');
        }

        $updateStatus = $query->update(array_merge($dataArray, $status));
        if (!$updateStatus) {
            throw new Exception('Failed to update approver status');
        }

        return $updateStatus;
    }

    /** Log Approvals */
    public function logApproval($service_id, $level, $type)
    {
        $logApproval = Approvals::create([
            'service_id' => $service_id,
            'level' => $level,
            'status' => self::PENDING,
            'type' => $type
        ]);

        if (!$logApproval) {
            throw new Exception('Failed to log approval details');
        }
    }

    /** Update Log Approvals */
    public function updateLogApproval($service_id, $current_level, $status, $type, $new_status, $remarks, $acted_at, $user_id = null)
    {
        $auth_user_id = Auth::user()->id;
        $query = Approvals::where([
            'service_id' => $service_id,
            'level' => $current_level,
            'status' => $status,
            'type' => $type,
        ])->first();

        if (!$query) {
            throw new Exception('Failed to update approval details');
        }

        $updated = $query->update([
            'service_id' => $service_id,
            'user_id' => $user_id == null ? $auth_user_id : null,
            'status' => $new_status,
            'remarks' => $remarks,
            'acted_at' => $acted_at
        ]);

        return $updated;
    }

    /** Get Next Approval */
    public function getNextApprovalLevel($current_level, $request_type, $receiving_option)
    {
        // $basedOnRequest = $request_type === EH::REQUEST_TYPE 
        $basedOnRequest = [];
        if ($request_type != EH::REQUEST_TYPE) { // if request_type is not Shipment/Delivery = 4
            $basedOnRequest = [EH::INSTALLATION_TL, EH::INSTALLING];
        } else {
            if ($receiving_option == EH::door_to_door) { // set in OUTBOUND Level
                $basedOnRequest = [EH::S_WIM, EH::ONGOING];
            }
            if ($receiving_option == EH::pickup) { // set in OUTBOUND Level
                $basedOnRequest = [EH::S_OUTBOUND, EH::ONGOING];
            }
        }

        $levels = [
            EH::IT_DEPARTMENT => [EH::SM_SER, EH::ONGOING],
            EH::SM_SER => [EH::WIM, EH::ONGOING],
            EH::WIM => [EH::SERVICE, EH::ONGOING],
            EH::SERVICE => [EH::BILLING_WIM, EH::ONGOING],
            EH::BILLING_WIM => [EH::OUTBOUND, EH::ONGOING],
            EH::OUTBOUND => $basedOnRequest,
            // EH::S_IT_DEPARTMENT => [EH::S_SM_SER, EH::ONGOING],
            // EH::S_SM_SER => [EH::S_WIM, EH::ONGOING],
            EH::S_WIM => [EH::S_BILLING_WIM, EH::ONGOING],
            // EH::S_SERVICE => [EH::S_BILLING_WIM, EH::ONGOING],
            EH::S_OUTBOUND => [EH::S_WIM, EH::ONGOING],
            EH::S_BILLING_WIM => [EH::INSTALLATION_TL, EH::INSTALLING],

            // EH::INSTALLATION_TL => [EH::INSTALLATION_ENGINEER, EH::INSTALLING],
            // EH::INSTALLATION_ENGINEER => [EH::EH_SIGNATORY_COMPLETE, EH::COMPLETE]
        ];

        return $levels[$current_level] ?? throw new Exception('Invalid approver');
    }

    //End of the Class
}
