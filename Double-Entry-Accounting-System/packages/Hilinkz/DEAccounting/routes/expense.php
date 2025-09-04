<?php

use Hilinkz\DEAccounting\Http\Controllers\ExpenseController;

Route::get('/expenses', [ExpenseController::class, 'index'])->name('de-expense.index');
Route::get('/expenses/new', [ExpenseController::class, 'create'])->name('de-expense.create');
Route::delete('/expenses/{id}', [ExpenseController::class, 'delete'])->name('de-expense.delete');
