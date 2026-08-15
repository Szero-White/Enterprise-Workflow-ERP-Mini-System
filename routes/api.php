<?php

use App\Http\Controllers\Api\V1\InventoryStockController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\PurchaseRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.basic', 'active', 'demo.safe', 'throttle:60,1'])
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::middleware('role:admin,manager,procurement,asset_manager')->group(function (): void {
            Route::get('items', [ItemController::class, 'index'])->name('items.index');
            Route::get('inventory-stocks', [InventoryStockController::class, 'index'])->name('inventory-stocks.index');
        });

        Route::middleware('role:employee,manager,procurement,finance,director,admin')->group(function (): void {
            Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
            Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
        });

        Route::middleware('role:employee,admin')->group(function (): void {
            Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
        });
    });
