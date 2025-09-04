<?php

use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchasePaymentController;

Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
Route::get('purchases/{purchase}/payments', [PurchasePaymentController::class, 'index'])->name('purchases.payments');
Route::get('purchases/{purchase}/payments/create', [PurchasePaymentController::class, 'create'])->name('purchases.payments.create');
Route::post('purchases/{purchase}/payments', [PurchasePaymentController::class, 'store'])->name('purchases.payments.store');
Route::delete('purchases/{purchase}/payments/{payment}', [PurchasePaymentController::class, 'destroy'])->name('purchases.payments.destroy');