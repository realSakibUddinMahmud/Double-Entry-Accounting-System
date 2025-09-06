<?php

// Role Routes
Route::prefix('roles')->middleware(['auth'])->group(function () {
    // List all roles
    Route::get('/', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    
    // Show create form
    Route::get('/create', [App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
    
    // Store new role
    Route::post('/', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    
    // Show single role
    Route::get('/{id}', [App\Http\Controllers\Admin\RoleController::class, 'show'])->name('roles.show');
    
    // Show edit form
    Route::get('/{id}/edit', [App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
    
    // Update role
    Route::put('/{id}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    
    // Delete role
    Route::delete('/{id}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
});