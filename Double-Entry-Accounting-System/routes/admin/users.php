<?php

// Role assignment routes
Route::get('/admin/users', [App\Http\Controllers\Admin\UserRoleController::class, 'index'])->name('admin.users.index');
Route::get('/admin/users/{user}/roles/edit', [App\Http\Controllers\Admin\UserRoleController::class, 'edit'])->name('admin.user-roles.edit');
Route::put('/admin/users/{user}/roles', [App\Http\Controllers\Admin\UserRoleController::class, 'update'])->name('admin.user-roles.update');

// User status toggle route
Route::patch('/admin/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserRoleController::class, 'toggleStatus'])->name('admin.users.toggle-status');
