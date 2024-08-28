<?php

use App\Http\Controllers\Admin\ApprovalConfiguration;
use App\Http\Controllers\Admin\PMSettings;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\authController;
use App\Http\Controllers\ehController\EhMainApproverController;
use App\Http\Controllers\ehController\EhMainController;
use App\Http\Controllers\ehController\InternalRequest;
use App\Http\Controllers\ehController\WorkOrder;
use App\Http\Controllers\Engineer\Engineer;
use App\Http\Controllers\EquipmentHandling\EquipmentHandlingController;
use App\Http\Controllers\PreventiveMaintenance\PreventiveMaintenance;
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

        Route::get('/get-equipment-handling', [EquipmentHandlingController::class, 'showBasedOnCondition']);
        Route::get('/get-specific-equipment-handling', [EquipmentHandlingController::class, 'getEquipmentHandlingById']);

    /* Equipment Handling */
    // Route::get('/get-equipment-handling-data', [EhMainController::class, 'show']);
        // Internal Request
        Route::get('/get-engineers-data', [InternalRequest::class, 'get_engineers_data']);
        Route::get('/get-internal-request-details', [InternalRequest::class, 'get_internal_request_details']);
        Route::post('/internal-process', [InternalRequest::class, 'add_internal_process']);
        Route::get('/checkIfDelegated', [InternalRequest::class, 'checkIfDelegated']);
        Route::get('/getInternalRequest', [InternalRequest::class, 'getInternalRequest']);
        // Route::get('/detailed-internal-request', [InternalRequest::class, 'getDetailedInternalRequest']);
        Route::post('/accept-internal-request', [InternalRequest::class, 'accept_internal_request']);
        Route::post('/internal-servicing-completed', [InternalRequest::class, 'internal_servicing_completed']);
        Route::post('/markPackedEndorsedToWarehouse', [InternalRequest::class, 'markPackedEndorsedToWarehouse']);
        Route::get('/internal-servicing-get-status', [InternalRequest::class, 'internal_servicing_get_status']);



    /** Approver */
        Route::get('/approver-get-equipment-handling-data', [EhMainApproverController::class, 'index']);
        Route::get('/get-equipments', [EhMainApproverController::class, 'get_equipments']);
        Route::post('/approve-request', [EhMainApproverController::class, 'approve_request']);
        Route::post('/disapprove-request', [EhMainApproverController::class, 'disapprove_request']);
        Route::get('/get-approval-history', [ApprovalConfiguration::class, 'approval_history']);



    /** Master Data and Submit Work Order*/
    Route::get('/master-data-equipments', [EhMainController::class, 'master_data_equipments']); // List of Equipments and 
    Route::get('/get_institution', [EhMainController::class, 'get_institution']); // List of  and Institution
    Route::post('/submit-work-order', [WorkOrder::class, 'submit_work_order']); // Submit Work Order


    /* Admin Account - subject to edit auth/middleware */
    Route::get('/users', [ApprovalConfiguration::class, 'index']);
    Route::post('/submit-approver', [ApprovalConfiguration::class, 'assign_approver']);
    Route::get('/get-approver', [ApprovalConfiguration::class, 'get_approvers_data']);
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
        Route::get('/get_permissions', [Roles::class, 'get_permissions']);
        Route::get('/get_modules', [Roles::class, 'get_modules']);
        Route::put('/set_permissions', [Roles::class, 'set_permissions']);
        Route::put('/set_modules', [Roles::class, 'set_modules']);
        Route::post('/add_role_name', [Roles::class, 'add_role_name']);
        Route::delete('/delete_role_name', [Roles::class, 'delete_role_name']);
        Route::post('/assign-role', [Roles::class, 'assign_role']);
        Route::delete('/delete-role', [Roles::class, 'delete_role']);
        Route::get('/assigned-user-role', [Roles::class, 'get_assigned_roles']);
        Route::get('/get-approver-roles', [Roles::class, 'get_approver_roles']);


    /** Team Leader */
    Route::get('/get-request-to-assign-installation', [TeamLeader::class, 'get_request_to_assign']);
    // Route::post('/assign-engineer', [TeamLeader::class, 'assign_engineer']);

    /** Engineer */
    Route::get('/get-assigned-request', [Engineer::class, 'get_assigned_request']);
    // Route::post('/accept-pm-task', [Engineer::class, '']);


    /** Preventive Maintenance */
    Route::get('/get-preventive-maintenance', [PreventiveMaintenance::class, 'get_preventive_maintenance']);
    Route::get('/get_pm_record_details', [PreventiveMaintenance::class, 'get_pm_record_details']);
    Route::post('/create-pm', [PreventiveMaintenance::class, 'create_preventive_maintenance']);
    Route::post('/pm_process', [PreventiveMaintenance::class, 'pm_process']);
    Route::post('/pm_accepted', [PreventiveMaintenance::class, 'pm_accepted']);
    Route::post('/pm_task_processing', [PreventiveMaintenance::class, 'pm_task_processing']);
        Route::get('/getStandardActions', [PreventiveMaintenance::class, 'getStandardActions']);


    /* Log Me Out */
    Route::post('/log-me-out', [authController::class, 'logmeout']);
});
