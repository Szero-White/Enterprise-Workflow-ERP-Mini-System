<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FormFieldController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkflowStepController;
use App\Http\Controllers\Admin\WorkflowTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\RequestSubmissionController;
use App\Http\Controllers\Manager\ApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::resource('form-templates', FormTemplateController::class)
            ->parameters(['form-templates' => 'formTemplate']);
        Route::resource('form-templates.fields', FormFieldController::class)
            ->parameters(['form-templates' => 'formTemplate', 'fields' => 'field'])
            ->except(['show']);

        Route::resource('workflow-templates', WorkflowTemplateController::class)
            ->parameters(['workflow-templates' => 'workflowTemplate']);
        Route::resource('workflow-templates.steps', WorkflowStepController::class)
            ->parameters(['workflow-templates' => 'workflowTemplate', 'steps' => 'step'])
            ->except(['show']);
    });

    Route::prefix('employee')->name('employee.')->middleware('role:employee,admin')->group(function () {
        Route::get('/requests', [RequestSubmissionController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [RequestSubmissionController::class, 'selectTemplate'])->name('requests.select-template');
        Route::get('/requests/create/{formTemplate}', [RequestSubmissionController::class, 'create'])->name('requests.create');
        Route::post('/requests/{formTemplate}', [RequestSubmissionController::class, 'store'])->name('requests.store');
        Route::get('/requests/{workflowRequest}', [RequestSubmissionController::class, 'show'])->name('requests.show');
        Route::get('/requests/{workflowRequest}/edit', [RequestSubmissionController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{workflowRequest}', [RequestSubmissionController::class, 'update'])->name('requests.update');
    });

    Route::prefix('manager')->name('manager.')->middleware('role:manager,hr,director,admin')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{workflowRequest}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{workflowRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{workflowRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{workflowRequest}/return', [ApprovalController::class, 'returnToEmployee'])->name('approvals.return');
    });
});
