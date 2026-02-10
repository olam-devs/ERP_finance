<?php

use App\Http\Controllers\SuperAdmin\SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\ImpersonationController;
use App\Http\Controllers\SuperAdmin\SuperAdminManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
|
| Here are all the routes for the super admin panel.
| These routes use the 'central' database connection.
|
*/

// Super Admin Authentication Routes (Guest only)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::middleware('guest:superadmin')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post');
    });

    Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
});

// Super Admin Protected Routes
Route::prefix('superadmin')->name('superadmin.')->middleware(['superadmin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // School Management
    Route::resource('schools', SchoolController::class);
    Route::post('/schools/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
    Route::post('/schools/{school}/reset-password', [SchoolController::class, 'resetAccountantPassword'])->name('schools.reset-password');
    Route::post('/schools/{school}/sms-credits', [SchoolController::class, 'updateSmsCredits'])->name('schools.sms-credits');
    Route::post('/schools/{school}/sync-name', [SchoolController::class, 'syncNameFromTenant'])->name('schools.sync-name');
    Route::post('/schools/{school}/sync-name-to-tenant', [SchoolController::class, 'syncNameToTenant'])->name('schools.sync-name-to-tenant');

    // Accountant Management for Schools
    Route::post('/schools/{school}/accountants', [SchoolController::class, 'addAccountant'])->name('schools.accountants.store');
    Route::put('/schools/{school}/accountants/{accountant}', [SchoolController::class, 'updateAccountant'])->name('schools.accountants.update');
    Route::post('/schools/{school}/accountants/{accountant}/toggle', [SchoolController::class, 'toggleAccountantStatus'])->name('schools.accountants.toggle');
    Route::delete('/schools/{school}/accountants/{accountant}', [SchoolController::class, 'deleteAccountant'])->name('schools.accountants.destroy');
    
    // Super Admin Management
    Route::get('/admins', [SuperAdminManagementController::class, 'index'])->name('admins.index');
    Route::post('/admins', [SuperAdminManagementController::class, 'store'])->name('admins.store');
    Route::put('/admins/{superAdmin}', [SuperAdminManagementController::class, 'update'])->name('admins.update');
    Route::post('/admins/{superAdmin}/toggle', [SuperAdminManagementController::class, 'toggleStatus'])->name('admins.toggle');
    Route::post('/admins/{superAdmin}/reset-password', [SuperAdminManagementController::class, 'resetPassword'])->name('admins.reset-password');

    // Profile / Change Password
    Route::get('/profile', [SuperAdminAuthController::class, 'profile'])->name('profile');
    Route::put('/profile/password', [SuperAdminAuthController::class, 'updatePassword'])->name('profile.update-password');

    // Impersonation
    Route::post('/impersonate/{school}', [ImpersonationController::class, 'impersonate'])->name('impersonate');
    Route::post('/stop-impersonation', [ImpersonationController::class, 'stopImpersonation'])->name('stop-impersonation');
    
    // Activity Logs
    Route::get('/activity-logs', [SuperAdminDashboardController::class, 'activityLogs'])->name('activity-logs');
    
    // Analytics
    Route::get('/analytics', function () {
        return view('superadmin.analytics');
    })->name('analytics');
});
