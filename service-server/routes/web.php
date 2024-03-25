<?php

use App\Http\Controllers\Admin\ApprovalConfiguration;
use App\Http\Controllers\authController;
use App\Http\Controllers\ehController\EhMainController;
use App\Http\Controllers\ehController\WorkOrder;
use App\Http\Controllers\MainDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
/*
 * Guest Accoounts
 */
Route::middleware(['guest'])->group(function () {
    Route::GET('/', fn () => view('pages.auth.login'))->name('login');
    Route::POST('/services-login', [authController::class, 'dashboardLogin'])->name('dashboardLogin');
});

/**Authenticated user middlewares */
Route::middleware(['auth:usersSecond'])->group(function () {
    /*
     * Main Dashboard
     */
    Route::GET('/main-dashboard', [MainDashboardController::class, 'index'])->name('mainDashboard');

    /*
     * Admin Page
     */
    Route::GET('/approval-configuration', [ApprovalConfiguration::class, 'index'])->name('ApprovalConfiguration');
    Route::POST('/assign-approver', [ApprovalConfiguration::class, 'assign_approver'])->name('AssignApprover');
    Route::DELETE('/delete-approver/{id}', [ApprovalConfiguration::class, 'delete_approver'])->name('DeleteApprover');

    /*
     * Equipment Handling Pages
     */
    Route::GET('/request/equipment-handling-main', [EhMainController::class, 'index'])->name('eh.main');
    Route::GET('/request/equipment-handling-work-order', [EhMainController::class, 'addWorkOrder'])->name('eh.addWorkOrder');
    Route::POST('/submit-work-order', [WorkOrder::class, 'submit_work_order'])->name('SubmitWorkOrder');
    // Route::get('/work-order', fn() => view('pages.EquipmentHandling.eh-workOrder'))->name('eh.workOrder');
});

/*
 * Log Me Out
 */
Route::POST('logmeout', [authController::class, 'logmeout'])->name('logmeout');
// });
