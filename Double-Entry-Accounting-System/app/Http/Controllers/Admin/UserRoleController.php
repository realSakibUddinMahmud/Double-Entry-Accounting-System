<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get the logged-in user's tenant_id
        $tenantId = auth()->user()->tenant_id;

        // If Super Admin (no tenant restriction), show all
        if (auth()->user()->hasRole('Super Admin')) {
            $user = DB::connection('landlord')
                ->table('users')
                ->leftJoin('companies', 'users.tenant_id', '=', 'companies.id')
                ->select('users.*', 'companies.name as company_name')
                ->orderBy('users.name')
                ->get();
        } else {
            // Otherwise, restrict to tenant's users only
            $user = DB::connection('landlord')
                ->table('users')
                ->leftJoin('companies', 'users.tenant_id', '=', 'companies.id')
                ->where('users.tenant_id', $tenantId)
                ->select('users.*', 'companies.name as company_name')
                ->orderBy('users.name')
                ->get();
        }

        return view('admin.users.index', compact('user'));
    }

    // List all Users
    public function index2()
    {
        // $user = User::orderBy('name')->get();
        $user = DB::connection('landlord')->table('users')->leftJoin('companies', 'users.tenant_id', '=', 'companies.id')
            ->select('users.*', 'companies.name as company_name')
            ->orderBy('users.name')
            ->get();

        return view('admin.users.index', compact('user'));
    }

    /**
     * Show the form for assigning roles to a user
     */
    public function edit($userId)
    {
        $user = User::findOrFail($userId);

        // If the logged-in user has the 'Super Admin' role, show all roles
        if (auth()->user()->hasRole('Super Admin')) {
            $roles = Role::orderBy('name')->get();
        } else {
            // Otherwise, exclude 'Super Admin' role
            $roles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();
        }
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.roles.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the user's roles
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'roles' => 'nullable|array',
            'roles.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Role::where('id', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the actual role models
        $roles = Role::whereIn('id', $request->roles ?: [])->get();

        // Sync roles
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Remove a role from a user
     */
    public function destroy($userId, $roleId)
    {
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);

        // Detach the role
        $user->removeRole($role);

        return redirect()->route('admin.users.index')
            ->with('success', 'Role removed from user successfully.');
    }

    /**
     * Toggle user status (Active/Inactive)
     */
    public function toggleStatus($userId)
    {
        $primaryDB = env('DB_DATABASE');
        $currentUser = auth()->user();   // logged in user

        $user = DB::table($primaryDB . '.users')
                ->where('id', $userId)
                ->first();

        $isAuthorized = $currentUser->hasRole('Super Admin') ||
            ($currentUser->hasRole('Company Admin') && $user->tenant_id !== null && $currentUser->tenant_id == $user->tenant_id);

        // Authorization check
        if (!$isAuthorized) {
            // abort(403, 'Unauthorized action.');
            return redirect()->route('admin.users.index')->with('error', 'Unauthorized action.');
        }

        // Prevent self-deactivation
        if ($user->id === $currentUser->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot change your own status.');
        }

        DB::beginTransaction();

        try {
            $newStatus = !$user->status;

            // 1. Update in landlord database
            DB::table($primaryDB . '.users')
                ->where('id', $userId)
                ->update(['status' => $newStatus]);

            // 2. If user belongs to a tenant, update in tenant database
            if ($user->tenant_id) {
                $company = DB::table($primaryDB . '.companies')
                    ->where('id', $user->tenant_id)
                    ->first();

                if ($company) {
                    DB::table($company->db_name . '.users')
                        ->where('id', $userId)
                        ->update(['status' => $newStatus]);
                }
            }

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }
}
