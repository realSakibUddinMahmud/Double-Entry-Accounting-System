<?php

Route::get('/additional-fields', [App\Http\Controllers\Admin\AdditionalFieldController::class, 'index'])->name('additional-fields.index');
Route::post('/additional-fields', [App\Http\Controllers\Admin\AdditionalFieldController::class, 'store'])->name('additional-fields.store');
Route::put('/additional-fields/{additional_field}', [App\Http\Controllers\Admin\AdditionalFieldController::class, 'update'])->name('additional-fields.update');
Route::delete('/additional-fields/{additional_field}', [App\Http\Controllers\Admin\AdditionalFieldController::class, 'destroy'])->name('additional-fields.destroy');