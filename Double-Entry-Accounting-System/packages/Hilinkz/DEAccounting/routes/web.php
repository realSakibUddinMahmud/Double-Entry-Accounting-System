<?php

use Illuminate\Support\Facades\Route;
use Hilinkz\DEAccounting\Http\Controllers\FileController;
use Hilinkz\DEAccounting\Http\Controllers\DeJournalController;

// Package Routes
Route::middleware(['web'])->prefix('de-accounting')->group(function () {
    Route::get('/files/download/{id}', [FileController::class, 'download'])->name('de-file.download');
    Route::delete('/files/{id}', [FileController::class, 'delete'])->name('de-file.delete');
    require base_path('packages/Hilinkz/DEAccounting/routes/account.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/fund-transfer.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/payment.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/income-revenue.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/loan-investment.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/loan-invreturn.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/security-deposit.php');
    require base_path('packages/Hilinkz/DEAccounting/routes/expense.php');  
    require base_path('packages/Hilinkz/DEAccounting/routes/journal.php');  
    require base_path('packages/Hilinkz/DEAccounting/routes/ledger.php');
});
