<?php

use Hilinkz\DEAccounting\Http\Controllers\AccountController;

Route::get('/accounts', [AccountController::class, 'index'])->name('de-account.index');
Route::get('/accounts/new', [AccountController::class, 'create'])->name('de-account.create');
Route::get('/accounts/{id}/latest-balance', [AccountController::class, 'latestBalance'])->name('de-account.latest-balance');

Route::get('/accounts/{id}/edit', [AccountController::class, 'edit'])->name('de-account.edit');
Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('de-account.update');
Route::delete('/accounts/{id}', [AccountController::class, 'delete'])->name('de-account.delete');
