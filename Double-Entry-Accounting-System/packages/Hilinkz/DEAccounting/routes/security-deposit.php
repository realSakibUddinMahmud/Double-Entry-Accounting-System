<?php

use Hilinkz\DEAccounting\Http\Controllers\SecurityDepositController;

Route::get('/security-deposits', [SecurityDepositController::class, 'index'])->name('de-security-deposit.index');
Route::get('/security-deposits/new', [SecurityDepositController::class, 'create'])->name('de-security-deposit.create');
Route::delete('/security-deposits/{id}', [SecurityDepositController::class, 'delete'])->name('de-security-deposit.delete');
