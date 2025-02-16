<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalConfigModel;
use Illuminate\Http\Request;

class AdminApprovalConfig extends Controller
{
    public function view(Request $request){
        $q = ApprovalConfigModel::where()->get();
    }
}
