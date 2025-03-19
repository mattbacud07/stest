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
             P::OPERATION_SERVICE => [P::COMPLETED, P::uninstalling],
        ];

        return $levels[$current_level] ?? throw new Exception('Invalid approver');
    }
}
