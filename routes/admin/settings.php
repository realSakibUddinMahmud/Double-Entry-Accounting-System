<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Settings routes
    Route::prefix('settings')->name('admin.settings.')->group(function () {
        
        // Main settings page
        Route::get('/', [SettingController::class, 'index'])->name('index');
        
        // Update settings
        Route::post('/update', [SettingController::class, 'update'])->name('update');
    });
});
