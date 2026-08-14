<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FormFieldController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkflowStepController;
use App\Http\Controllers\Admin\WorkflowTemplateController;
use App\Http\Controllers\Api\V1\InventoryStockController as ApiInventoryStockController;
use App\Http\Controllers\Api\V1\ProductController as ApiProductController;
use App\Http\Controllers\Api\V1\SalesOrderController as ApiSalesOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Catalog\ProductCategoryController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\RequestSubmissionController;
use App\Http\Controllers\Manager\ApprovalController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');


    Route::middleware('role:admin,manager')->group(function () {
        Route::prefix('internal-api/v1')->name('internal-api.v1.')->group(function () {
            Route::get('products', [ApiProductController::class, 'index'])->name('products.index');
            Route::get('inventory-stocks', [ApiInventoryStockController::class, 'index'])->name('inventory-stocks.index');
            Route::get('sales-orders', [ApiSalesOrderController::class, 'index'])->name('sales-orders.index');
            Route::get('sales-orders/{order}', [ApiSalesOrderController::class, 'show'])->name('sales-orders.show');
        });

        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::resource('categories', ProductCategoryController::class)->except(['show']);
            Route::resource('products', ProductController::class)->except(['show']);
        });

        Route::prefix('crm')->name('crm.')->group(function () {
            Route::resource('customers', CustomerController::class)->except(['show']);
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::resource('warehouses', WarehouseController::class)->except(['show']);
            Route::get('stocks', [InventoryController::class, 'index'])->name('stocks.index');
            Route::get('receipts/create', [InventoryController::class, 'createReceipt'])->name('receipts.create');
            Route::post('receipts', [InventoryController::class, 'storeReceipt'])->name('receipts.store');
        });

        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('orders', [SalesOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/create', [SalesOrderController::class, 'create'])->name('orders.create');
            Route::post('orders', [SalesOrderController::class, 'store'])->name('orders.store');
            Route::get('orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/confirm', [SalesOrderController::class, 'confirm'])->name('orders.confirm');
            Route::post('orders/{order}/cancel', [SalesOrderController::class, 'cancel'])->name('orders.cancel');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::resource('form-templates', FormTemplateController::class)
            ->parameters(['form-templates' => 'formTemplate']);
        Route::resource('form-templates.fields', FormFieldController::class)
            ->parameters(['form-templates' => 'formTemplate', 'fields' => 'field'])
            ->except(['show'])
            ->scoped();

        Route::resource('workflow-templates', WorkflowTemplateController::class)
            ->parameters(['workflow-templates' => 'workflowTemplate']);
        Route::resource('workflow-templates.steps', WorkflowStepController::class)
            ->parameters(['workflow-templates' => 'workflowTemplate', 'steps' => 'step'])
            ->except(['show'])
            ->scoped();
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
        Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/{workflowRequest}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{workflowRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{workflowRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{workflowRequest}/return', [ApprovalController::class, 'returnToEmployee'])->name('approvals.return');
    });
});
