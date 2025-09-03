<?php

// Permission Routes
Route::prefix('permissions')->middleware(['auth'])->group(function () {
    // List all permissions
    Route::get('/', [App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
    
    // Show create form
    Route::get('/create', [App\Http\Controllers\Admin\PermissionController::class, 'create'])->name('permissions.create');
    
    // Store new permission
    Route::post('/', [App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('permissions.store');
    
    // Show single permission
    Route::get('/{id}', [App\Http\Controllers\Admin\PermissionController::class, 'show'])->name('permissions.show');
    
    // Show edit form
    Route::get('/{id}/edit', [App\Http\Controllers\Admin\PermissionController::class, 'edit'])->name('permissions.edit');
    
    // Update permission
    Route::put('/{id}', [App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('permissions.update');
    
    // Delete permission
    Route::delete('/{id}', [App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('permissions.destroy');
});
