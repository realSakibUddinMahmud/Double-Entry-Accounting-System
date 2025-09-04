<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // List all permissions
    public function index()
    {
        $permissions = Permission::orderBy('id')->get();
        return view('admin.permissions.index', compact('permissions'));
    }

    // // Show create form
    // public function create()
    // {
    //     return view('admin.permissions.create');
    // }

    // Store new permission
    public function store(Request $request)
    {
        // Custom validation to check uniqueness in tenant database
        // $validator = Validator::make($request->all(), [
        //     'name' => [
        //         'required',
        //         'string',
        //         'max:255',
        //         function ($attribute, $value, $fail) {
        //             if (Permission::where('name', $value)->exists()) {
        //                 $fail('The '.$attribute.' has already been taken.');
        //             }
        //         }
        //     ],
        // ]);

        // if ($validator->fails()) {
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        // Split the input by commas and trim each value
        $permissionNames = collect(explode(',', $request->input('name')))
            ->map(fn($name) => trim($name))
            ->filter() // Remove any empty strings
            ->unique();

        // Validate: check each name for format and uniqueness
        $errors = [];

        foreach ($permissionNames as $name) {
            if (strlen($name) > 255) {
                $errors[] = "The permission '$name' exceeds the maximum length of 255 characters.";
            }

            if (!preg_match('/^[a-z0-9\-]+$/', $name)) {
                $errors[] = "The permission '$name' contains invalid characters. Only lowercase letters, numbers, and dashes are allowed.";
            }

            if (Permission::where('name', $name)->exists()) {
                $errors[] = "The permission '$name' already exists.";
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors(['name' => implode(' ', $errors)])
                ->withInput();
        }

        // All passed, create permissions
        foreach ($permissionNames as $name) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
        return back()->with('success', 'Permission added successfully!');
    }

    // // Show single permission
    // public function show($id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     return view('admin.permissions.show', compact('permission'));
    // }

    // // Show edit form
    // public function edit($id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     return view('admin.permissions.edit', compact('permission'));
    // }

    // Update permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        // Custom validation to check uniqueness in tenant database
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    if (Permission::where('name', $value)->where('id', '!=', $id)->exists()) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update permission
        $permission->update([
            'name' => $request->name,
        ]);

        // return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
        return back()->with('success', 'Permission updated successfully!');
    }

    // Delete permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        // return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
        return back()->with('success', 'Permission deleted successfully!');
    }
}
