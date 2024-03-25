<?php

use App\Http\Controllers\Admin\ApprovalConfiguration;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\authController;
use App\Http\Controllers\ehController\EhMainApproverController;
use App\Http\Controllers\ehController\EhMainController;
use App\Http\Controllers\ehController\WorkOrder;
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
    /* Equipment Handling */
    Route::get('/get-equipment-handling-data', [EhMainController::class, 'index']);

    /** Approver */
        Route::get('/approver-get-equipment-handling-data', [EhMainApproverController::class, 'index']);

    /** Submit Work Order */
    Route::get('/master-data-equipments', [EhMainController::class, 'addWorkOrder']); // List of Equipments
    Route::post('/submit-work-order', [WorkOrder::class, 'submit_work_order']); // Submit Work Order

    /* Admin Account - subject to edit auth/middleware */
    Route::get('/users', [ApprovalConfiguration::class, 'index']);
    Route::post('/submit-approver', [ApprovalConfiguration::class, 'assign_approver']);
    Route::get('/get-approver', [ApprovalConfiguration::class, 'get_approvers_data']);
    Route::delete('/delete-approver', [ApprovalConfiguration::class, 'delete_approver']);

    // Assign Role
    Route::post('/assign-role', [Roles::class, 'assign_role']);
    Route::get('/assigned-user-role', [Roles::class, 'get_assigned_roles']);

    /* Log Me Out */
    Route::post('/log-me-out', [authController::class, 'logmeout']);
});
