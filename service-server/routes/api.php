<?php

use App\Http\Controllers\Admin\AdminApprovalConfig;
use App\Http\Controllers\Admin\ApprovalConfiguration;
use App\Http\Controllers\Admin\Designation;
use App\Http\Controllers\Admin\PMSettings;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\Admin\SSUController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\authController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Corrective\CM;
use App\Http\Controllers\ehController\ChecklistItem;
use App\Http\Controllers\ehController\EhMainApproverController;
use App\Http\Controllers\ehController\EhMainController;
use App\Http\Controllers\ehController\EquipmentHandlingInstallation;
use App\Http\Controllers\ehController\InternalRequest;
use App\Http\Controllers\ehController\WorkOrder;
use App\Http\Controllers\Engineer\ServiceActionController;
use App\Http\Controllers\EquipmentHandling\EquipmentHandlingController;
use App\Http\Controllers\MasterData;
use App\Http\Controllers\PreventiveMaintenance\PMSparepartsUsed;
use App\Http\Controllers\PreventiveMaintenance\PreventiveMaintenance;
use App\Http\Controllers\PreventiveMaintenance\SendToCM;
use App\Http\Controllers\Pullout\Pullout;
use App\Http\Controllers\Pullout\PulloutUninstallation;
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

    Route::get('/users', [ApprovalConfiguration::class, 'index']);
    
    /** Route Middleware for Admin Account */
    Route::middleware(['adminMiddleware'])->group(function () {
        /* Admin Account - subject to edit auth/middleware */
        // Route::get('/users', [ApprovalConfiguration::class, 'index']);
        Route::post('/update_approver_config', [ApprovalConfiguration::class, 'update_approver_config']);
        Route::post('/assign_supervisor', [ApprovalConfiguration::class, 'assign_supervisor']);
        Route::post('/assign_sbu', [ApprovalConfiguration::class, 'assign_sbu']);
        Route::delete('/delete-approver', [ApprovalConfiguration::class, 'delete_approver']);
        //PM Settings
        Route::post('/save-pm-sched', [PMSettings::class, 'pm_schedule']);
        Route::put('/edit-pm-sched', [PMSettings::class, 'edit_pm_schedule']);
        Route::get('/get-pm-sched', [PMSettings::class, 'get_pm_schedule']);
        Route::post('/saveAction', [PMSettings::class, 'saveAction']);
        Route::delete('/deleteAction', [PMSettings::class, 'deleteAction']);
        Route::put('/editActions', [PMSettings::class, 'editActions']);


        // Assign Role Permission and Modules
        Route::get('/get_roles', [Roles::class, 'get_roles']);
        Route::get('/get_permission_per_role', [Roles::class, 'get_permission_per_role']);
        Route::put('/set_permissions_per_role', [Roles::class, 'set_permissions_per_role']);
        Route::post('/add_role_name', [Roles::class, 'add_role_name']);
        Route::put('/edit_role_name', [Roles::class, 'edit_role_name']);
        Route::delete('/delete_role_name', [Roles::class, 'delete_role_name']);
        Route::post('/assign-role', [Roles::class, 'assign_role']);
        Route::delete('/delete-role', [Roles::class, 'delete_role']);
        Route::get('/assigned-user-role', [Roles::class, 'get_assigned_roles']);
        Route::get('/get-approver-roles', [Roles::class, 'get_approver_roles']);
        Route::get('/get_designations', [Designation::class, 'get_designations']);

        // Route for Admin Approval Configuration
        Route::get('/get-approval-configuration', [AdminApprovalConfig::class, 'view']);
        

        /** SSU and designation */
        Route::prefix('item')->group(function(){
            Route::get('/', [SSUController::class, 'index']);
            Route::post('/store', [SSUController::class, 'store']);
            Route::put('/edit', [SSUController::class, 'edit']);
            Route::delete('/delete', [SSUController::class, 'delete']);
        });

        /** Action Logs */
        Route::get('/get_logs', [AdminApprovalConfig::class, 'get_logs']);
    });

    /** =============================== END OF ADMIN =================================== */
    Route::get('/getStandardActions', [PMSettings::class, 'getStandardActions']); //Acccessible to Service Engineer


    /** Master Data */
    Route::get('/get_master_data', [MasterData::class, 'get_master_data']);
    Route::post('/createMasterData', [MasterData::class, 'createMasterData']);
    Route::put('/editMasterData', [MasterData::class, 'editMasterData']);
    Route::get('/viewMasterDataByRowID', [MasterData::class, 'viewMasterDataByRowID']);
    Route::delete('/delete_master_data', [MasterData::class, 'delete_master_data']);

    /* Equipment Handling */
    Route::post('/submit-work-order', [WorkOrder::class, 'submit_work_order']); // Submit Work Order
    Route::get('/get-equipment-handling', [EquipmentHandlingController::class, 'index']);
    Route::get('/get-specific-equipment-handling', [EquipmentHandlingController::class, 'getEquipmentHandlingById']);
    Route::delete('/delete_eh/{id}', [EquipmentHandlingController::class, 'delete_eh']);

    /** Pullout Request */
    Route::get('/view_pullout', [Pullout::class, 'index']);
    Route::get('/view-pullout-id', [Pullout::class, 'view']);
    Route::post('/create-pullout', [Pullout::class, 'create']);
    Route::post('/approve-pullout', [Pullout::class, 'approve']);
    Route::post('/disapprove-pullout', [Pullout::class, 'disapproved']);

    /** Pullout Uninstallation */
    Route::post('/pr_delegate_engineer', [PulloutUninstallation::class, 'delegate_engineer']);


    /** Service Action Controller */
    Route::get('/get-engineers-data', [ServiceActionController::class, 'get_engineers_data']);
    // Route::update('/update_status_closed', [Controller::class, 'update_status_closed']);


    // Equipment Handling -> Internal Servicing
    Route::get('/getInternalRequest', [InternalRequest::class, 'index']);
    Route::get('/getInternalRowById', [InternalRequest::class, 'getInternalRowById']);
    Route::post('/internal-process', [InternalRequest::class, 'add_internal_process']);
    Route::post('/redelegation', [InternalRequest::class, 'redelegation']);
    Route::post('/for_storage', [InternalRequest::class, 'for_storage']);
    Route::post('/approve_by_wim', [InternalRequest::class, 'approve_by_wim']);
    Route::post('/accept-internal-request', [InternalRequest::class, 'accept_internal_request']);
    Route::post('/decline-internal-request', [InternalRequest::class, 'decline_internal_request']);
    Route::post('/internal-servicing-completed', [InternalRequest::class, 'internal_servicing_completed']);
    Route::get('/internal-servicing-get-status', [InternalRequest::class, 'internal_servicing_get_status']);

        Route::get('/getChecklistItem', [ChecklistItem::class, 'getChecklistItem']);
        Route::get('/getChecklistItemAcquired', [ChecklistItem::class, 'getChecklistItemAcquired']);

    /** == Equipment Handling - Delegation & Installation ===== */
    Route::post('/delegate-engineer', [EquipmentHandlingInstallation::class, 'delegate_engineer']);
    Route::post('/accept-decline-delegate', [EquipmentHandlingInstallation::class, 'accept_decline_delegate']);
    Route::post('/mark_as_completed', [EquipmentHandlingInstallation::class, 'mark_as_completed']);



    /** Approver */
    // Route::get('/approver-get-equipment-handling-data', [EhMainApproverController::class, 'index']);
    Route::get('/get-equipments', [EhMainApproverController::class, 'get_equipments']);
    Route::post('/approve-request', [EhMainApproverController::class, 'approve_request']); //->middleware(['permission:Equipment Handling,approve']);
    Route::post('/disapprove-request', [EhMainApproverController::class, 'disapprove_request']); //->middleware(['permission:Equipment Handling,approve']);;
    Route::get('/get-approval-history', [ApprovalConfiguration::class, 'approval_history']);



    /** Approvals */
    Route::get('/get-approvals', [ApprovalController::class, 'index']);

    /** Master Data and Submit Work Order*/
    Route::get('/master-data-equipments', [EhMainController::class, 'master_data_equipments']); // List of Equipments and 
    Route::get('/get_institution', [EhMainController::class, 'get_institution']); // List of  and Institution
    Route::get('/get_supplier', [EhMainController::class, 'get_supplier']); // List of  and Institution


    /** Preventive Maintenance */
    Route::get('/get-preventive-maintenance', [PreventiveMaintenance::class, 'get_preventive_maintenance']);
    Route::get('/get_pm_record_details', [PreventiveMaintenance::class, 'get_pm_record_details']);
    Route::post('/createPMRequest', [PreventiveMaintenance::class, 'createPMRequests']);
    Route::post('/pm_delegate_engineer', [PreventiveMaintenance::class, 'pm_delegate_engineer']);
    Route::post('/accept-decline-delegated', [PreventiveMaintenance::class, 'accept_decline']);
    Route::post('/in-transit', [PreventiveMaintenance::class, 'in_transit']);
    Route::post('/start-task', [PreventiveMaintenance::class, 'start_task']);
    Route::post('/pm_mark_as_completed', [PreventiveMaintenance::class, 'mark_as_completed']);
    Route::delete('/delete_pm/{id}', [PreventiveMaintenance::class, 'delete_pm']);
    
    
    /** Corrective Maintenance */
    Route::get('/get-corrective-maintenance', [CM::class, 'get_all']);
    Route::get('/get_cm_record_details', [CM::class, 'get_cm_record_details']);
    // Route::post('/createPMRequest', [PreventiveMaintenance::class, 'createPMRequests']);
    Route::post('/pm_delegate_engineer', [CM::class, 'pm_delegate_engineer']);
    Route::post('/accept-decline-delegated', [CM::class, 'accept_decline']);
    Route::post('/in-transit', [CM::class, 'in_transit']);
    Route::post('/start-task', [CM::class, 'start_task']);
    Route::post('/cm_mark_as_completed', [CM::class, 'mark_as_completed']);
    Route::delete('/delete_cm/{id}', [CM::class, 'delete_cm']);

    // Route::get('/spareparts', [PMSparepartsUsed::class, 'view']);
    Route::post('/sendToCM', [SendToCM::class, 'sendToCM']); //send to CM
    Route::post('/setDaysObservation', [SendToCM::class, 'setDaysObservation']); //send to CM


    /** Get Auth User */
    Route::get('/get_role_permissions', [authController::class, 'get_role_permissions']);
    Route::get('/get_approval_level_config', [authController::class, 'get_user_assigned_roles']);


    /* Log Me Out */
    Route::post('/log-me-out', [authController::class, 'logmeout']);
});
