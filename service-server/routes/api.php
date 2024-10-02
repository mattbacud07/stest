<?php

use App\Http\Controllers\Admin\ApprovalConfiguration;
use App\Http\Controllers\Admin\Designation;
use App\Http\Controllers\Admin\PMSettings;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\Admin\SSUController;
use App\Http\Controllers\authController;
use App\Http\Controllers\ehController\EhMainApproverController;
use App\Http\Controllers\ehController\EhMainController;
use App\Http\Controllers\ehController\InternalRequest;
use App\Http\Controllers\ehController\WorkOrder;
use App\Http\Controllers\Engineer\Engineer;
use App\Http\Controllers\EquipmentHandling\EquipmentHandlingController;
use App\Http\Controllers\PreventiveMaintenance\PMSparepartsUsed;
use App\Http\Controllers\PreventiveMaintenance\PreventiveMaintenance;
use App\Http\Controllers\PreventiveMaintenance\SendToCM;
use App\Http\Controllers\TeamLeader\TeamLeader;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/* Authentication */
Route::post('/authentication', [authController::class, 'dashboardLogin']);

Route::middleware(['auth:sanctum'])->group(function () {

    /** Route Middleware for Admin Account */
    Route::middleware(['adminMiddleware'])->group(function () {
        /* Admin Account - subject to edit auth/middleware */
        Route::get('/users', [ApprovalConfiguration::class, 'index']);
        Route::post('/submit-approver', [ApprovalConfiguration::class, 'assign_approver']);
        Route::delete('/delete-approver', [ApprovalConfiguration::class, 'delete_approver']);
        //PM Settings
        Route::post('/save-pm-sched', [PMSettings::class, 'pm_schedule']);
        Route::put('/edit-pm-sched', [PMSettings::class, 'edit_pm_schedule']);
        Route::get('/get-pm-sched', [PMSettings::class, 'get_pm_schedule']);
        Route::get('/getStandardActions', [PMSettings::class, 'getStandardActions']);
        Route::post('/saveAction', [PMSettings::class, 'saveAction']);
        Route::delete('/deleteAction', [PMSettings::class, 'deleteAction']);
        Route::put('/editActions', [PMSettings::class, 'editActions']);


        // Assign Role Permission and Modules
        Route::get('/get_role_name', [Roles::class, 'get_role_name']);
        Route::get('/get_permission_per_role', [Roles::class, 'get_permission_per_role']);
        Route::put('/set_permissions_per_role', [Roles::class, 'set_permissions_per_role']);
        Route::post('/add_role_name', [Roles::class, 'add_role_name']);
        Route::delete('/delete_role_name', [Roles::class, 'delete_role_name']);
        Route::post('/assign-role', [Roles::class, 'assign_role']);
        Route::delete('/delete-role', [Roles::class, 'delete_role']);
        Route::get('/assigned-user-role', [Roles::class, 'get_assigned_roles']);
        Route::get('/get-approver-roles', [Roles::class, 'get_approver_roles']);
        Route::get('/get_designations', [Designation::class, 'get_designations']);

        /** SSU and designation */
        Route::prefix('ssu')->group(function(){
            Route::get('/', [SSUController::class, 'index']);
            Route::post('/store', [SSUController::class, 'store']);
            Route::put('/edit', [SSUController::class, 'edit']);
            Route::delete('/delete', [SSUController::class, 'delete']);
        });
    });


    /* Equipment Handling */
    Route::post('/submit-work-order', [WorkOrder::class, 'submit_work_order']); // Submit Work Order
    Route::get('/get-equipment-handling', [EquipmentHandlingController::class, 'showBasedOnCondition']);
    Route::get('/get-specific-equipment-handling', [EquipmentHandlingController::class, 'getEquipmentHandlingById']);
    Route::get('/get-approver', [ApprovalConfiguration::class, 'get_approvers_data']); // use also both in Approver and admin


    // Equipment Handling -> Internal Servicing
    Route::get('/get-engineers-data', [InternalRequest::class, 'get_engineers_data']);
    Route::get('/get-internal-request-details', [InternalRequest::class, 'get_internal_request_details']);
    Route::post('/internal-process', [InternalRequest::class, 'add_internal_process']);
    Route::get('/getInternalRequest', [InternalRequest::class, 'getInternalRequest']);
    Route::post('/accept-internal-request', [InternalRequest::class, 'accept_internal_request']);
    Route::post('/internal-servicing-completed', [InternalRequest::class, 'internal_servicing_completed']);
    Route::post('/markPackedEndorsedToWarehouse', [InternalRequest::class, 'markPackedEndorsedToWarehouse']);
    Route::get('/internal-servicing-get-status', [InternalRequest::class, 'internal_servicing_get_status']);



    /** Approver */
    // Route::get('/approver-get-equipment-handling-data', [EhMainApproverController::class, 'index']);
    Route::get('/get-equipments', [EhMainApproverController::class, 'get_equipments']);
    Route::post('/approve-request', [EhMainApproverController::class, 'approve_request']); //->middleware(['permission:Equipment Handling,approve']);
    Route::post('/disapprove-request', [EhMainApproverController::class, 'disapprove_request']); //->middleware(['permission:Equipment Handling,approve']);;
    Route::get('/get-approval-history', [ApprovalConfiguration::class, 'approval_history']);



    /** Master Data and Submit Work Order*/
    Route::get('/master-data-equipments', [EhMainController::class, 'master_data_equipments']); // List of Equipments and 
    Route::get('/get_institution', [EhMainController::class, 'get_institution']); // List of  and Institution


    /** Team Leader */
    Route::get('/get-request-to-assign-installation', [TeamLeader::class, 'get_request_to_assign']);

    /** Engineer */
    Route::get('/get-assigned-request', [Engineer::class, 'get_assigned_request']);


    /** Preventive Maintenance */
    Route::get('/get-preventive-maintenance', [PreventiveMaintenance::class, 'get_preventive_maintenance']);
    Route::get('/get_pm_record_details', [PreventiveMaintenance::class, 'get_pm_record_details']);
    Route::post('/create-pm', [PreventiveMaintenance::class, 'create_preventive_maintenance']);
    Route::post('/pm_process', [PreventiveMaintenance::class, 'pm_process']);
    Route::post('/pm_accepted', [PreventiveMaintenance::class, 'pm_accepted']);
    Route::post('/pm_decline', [PreventiveMaintenance::class, 'pm_decline']);
    Route::post('/pm_task_processing', [PreventiveMaintenance::class, 'pm_task_processing']);
    Route::get('/getStandardActions', [PreventiveMaintenance::class, 'getStandardActions']);

    Route::get('/spareparts', [PMSparepartsUsed::class, 'view']);
    Route::post('/sendToCM', [SendToCM::class, 'sendToCM']); //send to CM
    Route::post('/setDaysObservation', [SendToCM::class, 'setDaysObservation']); //send to CM


    /** Get Auth User */
    Route::get('/get_role_permissions', [authController::class, 'get_role_permissions']);


    /* Log Me Out */
    Route::post('/log-me-out', [authController::class, 'logmeout']);
});
