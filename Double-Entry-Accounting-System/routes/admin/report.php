<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportController;

Route::get('report/sales', [ReportController::class, 'sales'])->name('report.sales');
Route::get('report/purchase', [ReportController::class, 'purchase'])->name('report.purchase');
Route::get('report/stock', [ReportController::class, 'stock'])->name('report.stock');

// Export routes for sales report
Route::get('report/sales/export', [ReportController::class, 'salesExport'])->name('report.sales.export');
Route::get('report/purchase/export', [ReportController::class, 'purchaseExport'])->name('report.purchase.export');
Route::get('report/stock/export', [ReportController::class, 'stockExport'])->name('report.stock.export');

Route::get('report/income-statement', [ReportController::class, 'incomeStatement'])->name('report.income-statement');
Route::get('report/income-statement/export', [ReportController::class, 'incomeStatementExport'])->name('report.income-statement.export');
Route::get('report/account-transactions/{accountId}', [ReportController::class, 'accountTransactions'])->name('report.account-transactions');

Route::get('report/balance-sheet', [ReportController::class, 'balanceSheet'])->name('report.balance-sheet');
Route::get('report/trial-balance', [ReportController::class, 'trialBalance'])->name('report.trial-balance');