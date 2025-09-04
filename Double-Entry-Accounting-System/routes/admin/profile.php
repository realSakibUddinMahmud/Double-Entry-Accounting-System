<?php

Route::prefix('profile')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Auth\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/edit', [App\Http\Controllers\Auth\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/', [App\Http\Controllers\Auth\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [App\Http\Controllers\Auth\ProfileController::class, 'updatePassword'])->name('profile.password.update');
});