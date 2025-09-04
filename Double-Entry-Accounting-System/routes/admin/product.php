<?php

Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');