<?php

use Hilinkz\DEAccounting\Http\Controllers\FundTransferController;

Route::get('/fund-transfers', [FundTransferController::class, 'index'])->name('de-fund-transfer.index');
Route::get('/fund-transfers/new', [FundTransferController::class, 'create'])->name('de-fund-transfer.create');
Route::delete('/fund-transfers/{id}', [FundTransferController::class, 'delete'])->name('de-fund-transfer.delete');
