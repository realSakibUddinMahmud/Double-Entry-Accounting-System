<?php

use Hilinkz\DEAccounting\Http\Controllers\LoanInvReturnController;

Route::get('/loan-invreturns', [LoanInvReturnController::class, 'index'])->name('de-loan-invreturn.index');
Route::get('/loan-invreturns/new', [LoanInvReturnController::class, 'create'])->name('de-loan-invreturn.create');
Route::delete('/loan-invreturns/{id}', [LoanInvReturnController::class, 'delete'])->name('de-loan-invreturn.delete');
