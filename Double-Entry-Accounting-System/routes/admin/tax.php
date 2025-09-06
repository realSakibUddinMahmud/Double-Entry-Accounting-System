<?php

Route::get('/taxes', [App\Http\Controllers\Admin\TaxController::class, 'index'])->name('taxes.index');
Route::post('/taxes', [App\Http\Controllers\Admin\TaxController::class, 'store'])->name('taxes.store');
Route::put('/taxes/{tax}', [App\Http\Controllers\Admin\TaxController::class, 'update'])->name('taxes.update');
Route::delete('/taxes/{tax}', [App\Http\Controllers\Admin\TaxController::class, 'destroy'])->name('taxes.destroy');