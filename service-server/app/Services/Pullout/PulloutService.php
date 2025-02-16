<?php

namespace App\Services\Pullout;

use App\Models\EhServicesModel;
use App\Models\Pullout as P;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PulloutService
{
    
    public function approve_request($service_id, $level, $status)
    {
        $user_id = Auth::user()->id;
        $query = P::find($service_id);

        $statusArray = [];
        if($status !== null){
            $statusArray = ['status' => $status];
        }

        $update = $query->update(array_merge(['level' => $level], $statusArray));

        return $update > 0;
    }

/** Get Next Approval */
    public function pulloutNextApprovalLevel($current_level)
    {
        $levels = [
             P::SUPERVISOR => [P::OPERATION_SERVICE, P::pending],
             P::OPERATION_SERVICE => [P::OPERATION_SERVICE_DELEGATION, P::pending],
            //  EH::WIM => [EH::SERVICE, EH::ONGOING],
            //  EH::SERVICE => [EH::BILLING_WIM, EH::ONGOING],
            //  EH::BILLING_WIM => [EH::OUTBOUND, EH::ONGOING],
            //  EH::INSTALLATION_TL => [EH::INSTALLATION_ENGINEER, EH::INSTALLING],
            //  EH::INSTALLATION_ENGINEER => [EH::EH_SIGNATORY_COMPLETE, EH::COMPLETE]
        ];

        return $levels[$current_level] ?? throw new Exception('Invalid approver');
    }
}
