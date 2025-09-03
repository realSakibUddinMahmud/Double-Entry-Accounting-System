<?php

use Hilinkz\DEAccounting\Http\Controllers\IncomeRevenueController;

Route::get('/income-revenues', [IncomeRevenueController::class, 'index'])->name('de-income-revenue.index');
Route::get('/income-revenues/new', [IncomeRevenueController::class, 'create'])->name('de-income-revenue.create');
Route::delete('/income-revenues/{id}', [IncomeRevenueController::class, 'delete'])->name('de-income-revenue.delete');
