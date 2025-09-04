<?php

Route::get('/units', [App\Http\Controllers\Admin\UnitController::class, 'index'])->name('units.index');
Route::post('/units', [App\Http\Controllers\Admin\UnitController::class, 'store'])->name('units.store');
Route::put('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'update'])->name('units.update');
Route::delete('/units/{unit}', [App\Http\Controllers\Admin\UnitController::class, 'destroy'])->name('units.destroy');