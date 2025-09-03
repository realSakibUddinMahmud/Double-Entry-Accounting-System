<?php

Route::get('/companies', [App\Http\Controllers\Admin\CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/create', [App\Http\Controllers\Admin\CompanyController::class, 'create'])->name('companies.create');
Route::post('/companies', [App\Http\Controllers\Admin\CompanyController::class, 'store'])->name('companies.store');
Route::put('/companies/{company}', [App\Http\Controllers\Admin\CompanyController::class, 'update'])->name('companies.update');
Route::delete('/companies/{company}', [App\Http\Controllers\Admin\CompanyController::class, 'destroy'])->name('companies.destroy');
Route::get('/companies/{company}/edit', [App\Http\Controllers\Admin\CompanyController::class, 'edit'])->name('companies.edit');

Route::get('/companies/{company}/get-users', [App\Http\Controllers\Admin\CompanyController::class, 'getUsers'])->name('companies.get-users');
Route::post('/companies/{company}/assign-user', [App\Http\Controllers\Admin\CompanyController::class, 'assignUser'])->name('companies.assign-user');

Route::get('/companies/{company}/users', [App\Http\Controllers\Admin\CompanyController::class, 'companyUsers'])->name('companies.users');
Route::delete('/companies/{company}/users/{user}', [App\Http\Controllers\Admin\CompanyController::class, 'removeUser'])->name('companies.users.remove');

// For company admin
Route::get('/company/profile', [App\Http\Controllers\Admin\CompanyController::class, 'companyProfile'])->name('company.profile');
Route::get('/company/profile/edit', [App\Http\Controllers\Admin\CompanyController::class, 'editCompanyProfile'])->name('company.profile.edit');
Route::put('/company/profile', [App\Http\Controllers\Admin\CompanyController::class, 'updateCompanyProfile'])->name('company.profile.update');
