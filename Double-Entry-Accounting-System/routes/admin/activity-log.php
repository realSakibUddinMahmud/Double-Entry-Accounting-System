<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ActivityLogController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log.index');
    Route::get('/activity-log/sale/{saleId}', [ActivityLogController::class, 'getSaleActivities'])->name('admin.activity-log.sale');
    Route::get('/activity-log/purchase/{purchaseId}', [ActivityLogController::class, 'getPurchaseActivities'])->name('admin.activity-log.purchase');
});
