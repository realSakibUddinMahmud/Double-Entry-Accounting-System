<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // List all roles
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
    
        return view('admin.roles.index', compact('roles', 'permissions'));
        // return view('admin.roles.index', compact('roles'));
    }

    // // Show create form
    // public function create()
    // {
    //     $permissions = Permission::orderBy('name')->get();
    //     return view('admin.roles.create', compact('permissions'));
    // }

    // Store new role
    public function store(Request $request)
    {
        // Custom validation to check uniqueness in tenant database
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (Role::where('name', $value)->exists()) {
                        $fail('The '.$attribute.' has already been taken.');
                    }
                }
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Permission::where('id', $value)->exists()) {
                        $fail('The selected permission is invalid.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create role
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web', // default guard
        ]);

        // Assign permissions if any
        if ($request->has('permissions')) {
            // Get the actual permission models
            $permissions = Permission::whereIn('id', $request->permissions ?: [])->get();

            // Sync using permission models instead of IDs
            $role->syncPermissions($permissions);
        }

        // return redirect()->route('roles.index')->with('success', 'Role created successfully.');
        return back()->with('success', 'Role added successfully!');
    }

    // // Show single role
    // public function show($id)
    // {
    //     $role = Role::findOrFail($id);
    //     $rolePermissions = $role->permissions;
    //     return view('admin.roles.show', compact('role', 'rolePermissions'));
    // }

    // // Show edit form
    // public function edit($id)
    // {
    //     $role = Role::findOrFail($id);
    //     $permissions = Permission::orderBy('name')->get();
    //     $rolePermissions = $role->permissions->pluck('id')->toArray();
        
    //     return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    // }

    // Update role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Custom validation to check uniqueness in tenant database
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    if (Role::where('name', $value)->where('id', '!=', $id)->exists()) {
                        $fail('The '.$attribute.' has already been taken.');
                    }
                }
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Permission::where('id', $value)->exists()) {
                        $fail('The selected permission is invalid.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update role
        $role->update([
            'name' => $request->name,
        ]);

    // Get the actual permission models
    $permissions = Permission::whereIn('id', $request->permissions ?: [])->get();

    // Sync using permission models instead of IDs
    $role->syncPermissions($permissions);

        // return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
        return back()->with('success', 'Role updated successfully!');
    }

    // Delete role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        // return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
        return back()->with('success', 'Role deleted successfully!');
    }
}