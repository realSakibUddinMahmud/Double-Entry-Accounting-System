<?php

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\RolePermissionSeederController;

// Database refresh route (Views and Procedures) with secret key protection
Route::get('/refresh-database', function (\Illuminate\Http\Request $request) {
    $secret = $request->query('secret');
    if ($secret != 'admin1234') {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access.'
        ], 401);
    }

    try {
        $output = [];

        // Refresh Views
        Artisan::call('db:seed', ['--class' => 'ViewSeeder']);
        $output['views'] = Artisan::output();

        // Refresh Procedures
        Artisan::call('db:seed', ['--class' => 'ProcedureSeeder']);
        $output['procedures'] = Artisan::output();

        return response()->json([
            'success' => true,
            'message' => 'Database views and procedures refreshed successfully!',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to refresh database: ' . $e->getMessage()
        ], 500);
    }
})->name('refresh.database');

// Role Permission refresh route using Controller
Route::get('/refresh-roles-permissions', [RolePermissionSeederController::class, 'refresh'])->name('refresh.roles.permissions');

Route::get('/clear-all/{id}', function ($id) {
    // Check if the user is logged in
    if (!Auth::check()) {
        return redirect('/login')->with('message', 'Please log in to access this feature.');
    }

    // Check for correct PIN
    if ($id == 'admin1234') {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        // Account::fixTree();
        return redirect('/home')->with('message', 'Cache and views cleared successfully.');
    } else {
        return 'Sorry, wrong pin.';
    }
})->middleware('auth')->name('clear.all');