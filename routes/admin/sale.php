<?php

use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SalePaymentController;

Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::get('sales/{sale}/payments', [SalePaymentController::class, 'index'])->name('sales.payments');
Route::get('sales/{sale}/payments/create', [SalePaymentController::class, 'create'])->name('sales.payments.create');
Route::post('sales/{sale}/payments', [SalePaymentController::class, 'store'])->name('sales.payments.store');
Route::delete('sales/{sale}/payments/{payment}', [SalePaymentController::class, 'destroy'])->name('sales.payments.destroy');