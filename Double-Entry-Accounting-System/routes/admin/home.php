<?php

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('dashboard/summary-data', [App\Http\Controllers\HomeController::class, 'summaryData'])->name('dashboard.summary-data');