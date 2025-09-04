<?php

// Route::get('/units', [App\Http\Controllers\Admin\UnitController::class, 'index'])->name('units.index');
// Route::post('/units', [App\Http\Controllers\Admin\UnitController::class, 'store'])->name('units.store');
// Route::put('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'update'])->name('units.update');
// Route::delete('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'destroy'])->name('units.destroy');

Route::middleware(['auth'])->group(function () {
    // Customer Routes
    Route::get('/customers', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [App\Http\Controllers\Admin\CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [App\Http\Controllers\Admin\CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'destroy'])->name('customers.destroy');
});