<?php
use App\Http\Controllers\Admin\StockAdjustmentController;

// Stock Adjustment CRUD routes (no payment)
Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
    Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
    Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create');
    Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
    Route::get('/{stock_adjustment}/edit', [StockAdjustmentController::class, 'edit'])->name('edit');
    Route::put('/{stock_adjustment}', [StockAdjustmentController::class, 'update'])->name('update');
    Route::delete('/{stock_adjustment}', [StockAdjustmentController::class, 'destroy'])->name('destroy');
    Route::get('/{stock_adjustment}', [StockAdjustmentController::class, 'show'])->name('show');
});