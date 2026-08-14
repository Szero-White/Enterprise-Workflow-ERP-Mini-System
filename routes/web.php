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
use App\Http\Controllers\Api\V1\ItemController as ApiItemController;
use App\Http\Controllers\Asset\AssetAssignmentController;
use App\Http\Controllers\Asset\AssetController;
use App\Http\Controllers\Asset\AssetReturnController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\RequestSubmissionController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Manager\ApprovalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Procurement\GoodsReceiptController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\PurchaseRequestController;
use App\Http\Controllers\Procurement\SupplierController;
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

    Route::middleware('role:admin,manager,procurement,asset_manager')->group(function () {
        Route::prefix('internal-api/v1')->name('internal-api.v1.')->group(function () {
            Route::get('items', [ApiItemController::class, 'index'])->name('items.index');
            Route::get('inventory-stocks', [ApiInventoryStockController::class, 'index'])->name('inventory-stocks.index');
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::resource('item-categories', ItemCategoryController::class)->except(['show']);
            Route::resource('items', ItemController::class)->except(['show']);
            Route::resource('warehouses', WarehouseController::class)->except(['show']);
            Route::get('stocks', [InventoryController::class, 'index'])->name('stocks.index');
            Route::get('receipts/create', [InventoryController::class, 'createReceipt'])->name('receipts.create');
            Route::post('receipts', [InventoryController::class, 'storeReceipt'])->name('receipts.store');
        });
    });


    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::middleware('role:employee,admin')->group(function () {
            Route::get('purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
            Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
            Route::get('purchase-requests/{purchaseRequest}/edit', [PurchaseRequestController::class, 'edit'])->name('purchase-requests.edit');
            Route::put('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update'])->name('purchase-requests.update');
        });

        Route::middleware('role:employee,manager,procurement,finance,director,admin')->group(function () {
            Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
            Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
        });

        Route::middleware('role:procurement,admin')->group(function () {
            Route::resource('suppliers', SupplierController::class)->except(['show']);
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::get('purchase-orders/create/{purchaseRequest}', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
            Route::post('purchase-orders/{purchaseRequest}', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
            Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
            Route::post('purchase-orders/{purchaseOrder}/issue', [PurchaseOrderController::class, 'issue'])->name('purchase-orders.issue');
            Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

            Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
            Route::get('goods-receipts/create/{purchaseOrder}', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
            Route::post('goods-receipts/{purchaseOrder}', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
            Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
        });
    });


    Route::prefix('assets')->name('assets.')->group(function () {
        Route::middleware('role:asset_manager,procurement,admin')->group(function () {
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        });

        Route::middleware('role:asset_manager,admin')->group(function () {
            Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
            Route::get('/{asset}/assign', [AssetAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assignments.store');
            Route::get('/assignments/{assignment}/return', [AssetReturnController::class, 'create'])->name('returns.create');
            Route::post('/assignments/{assignment}/return', [AssetReturnController::class, 'store'])->name('returns.store');
            Route::post('/{asset}/maintenance/complete', [AssetController::class, 'releaseMaintenance'])->name('maintenance.complete');
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

    Route::prefix('manager')->name('manager.')->middleware('role:manager,hr,procurement,finance,director,admin')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/{workflowRequest}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{workflowRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{workflowRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{workflowRequest}/return', [ApprovalController::class, 'returnToEmployee'])->name('approvals.return');
    });
});
