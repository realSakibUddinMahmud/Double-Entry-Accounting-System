<?php

Route::get('/stores', [App\Http\Controllers\Admin\StoreController::class, 'index'])->name('stores.index');
Route::post('/stores', [App\Http\Controllers\Admin\StoreController::class, 'store'])->name('stores.store');
Route::put('/stores/{store}', [App\Http\Controllers\Admin\StoreController::class, 'update'])->name('stores.update');
Route::delete('/stores/{store}', [App\Http\Controllers\Admin\StoreController::class, 'destroy'])->name('stores.destroy');