<?php

use Hilinkz\DEAccounting\Http\Controllers\LoanInvestmentController;

Route::get('/loan-investments', [LoanInvestmentController::class, 'index'])->name('de-loan-investment.index');
Route::get('/loan-investments/new', [LoanInvestmentController::class, 'create'])->name('de-loan-investment.create');
Route::delete('/loan-investments/{id}', [LoanInvestmentController::class, 'delete'])->name('de-loan-investment.delete');
