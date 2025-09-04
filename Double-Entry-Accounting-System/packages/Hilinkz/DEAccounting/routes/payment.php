<?php

use Hilinkz\DEAccounting\Http\Controllers\PaymentController;

Route::get('/payments', [PaymentController::class, 'index'])->name('de-payment.index');
Route::get('/payments/new', [PaymentController::class, 'create'])->name('de-payment.create');
Route::delete('/payments/{id}', [PaymentController::class, 'delete'])->name('de-payment.delete');
