<?php

// Route::get('/units', [App\Http\Controllers\Admin\UnitController::class, 'index'])->name('units.index');
// Route::post('/units', [App\Http\Controllers\Admin\UnitController::class, 'store'])->name('units.store');
// Route::put('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'update'])->name('units.update');
// Route::delete('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'destroy'])->name('units.destroy');

Route::middleware(['auth'])->group(function () {
    // Supplier Routes
    Route::get('/suppliers', [App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [App\Http\Controllers\Admin\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{id}', [App\Http\Controllers\Admin\SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{id}/edit', [App\Http\Controllers\Admin\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{id}', [App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}', [App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('suppliers.destroy');
});
