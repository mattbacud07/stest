<?php

namespace App\Services\Validation;

use App\Models\ApprovalConfigModel;
use App\Models\RoleUser;
use Illuminate\Support\Facades\Auth;

class UserRolesValidator
{
    /** Check if user has Role base on current role from frontend */
    public function userHasRole($current_role){
        $user_id = Auth::user()->id;

        $getRole = RoleUser::where([
            'user_id' => $user_id,
            'role_id' => $current_role,
        ])->first();

        return $getRole !== null;
    }

    /** get_user_approval_level */
    public function getUserApprovalLevel($approver_category){
        $user_id = Auth::user()->id;

        $get_levels = ApprovalConfigModel::where([
            'user_id' => $user_id,
            'approver_category' => $approver_category
        ])->pluck('approval_level');

        return $get_levels->toArray();
    }

}