<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Brand::orderBy('name');
        
        // Search by brand name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Filter by status (if you have status field)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $brands = $query->paginate(20)->withQueryString();
        
        return view('admin.brand.index', compact('brands'));
    }

    public function index2()
    {
        $brands = Brand::all();
        // return view('admin.roles.index', compact('brands'));
        return view('admin.roles.roles-permissions', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->name);

        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $logoPath = null;
        $brand = new Brand([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);
        $brand->save();

        if ($request->hasFile('logo')) {
            $extension = $request->file('logo')->getClientOriginalExtension();
            $logoName = $slug . '-' . $brand->id . '.' . $extension;
            $logoPath = $request->file('logo')->storeAs('brands', $logoName, 'public');
            $brand->logo = $logoPath;
            $brand->save();
        }

        return back()->with('success', 'Brand added successfully!');
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->name);

        // Ensure unique slug except for current brand
        $originalSlug = $slug;
        $count = 1;
        while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $brand->name = $request->name;
        $brand->slug = $slug;
        $brand->description = $request->description;

        if ($request->hasFile('logo')) {
            $extension = $request->file('logo')->getClientOriginalExtension();
            $logoName = $slug . '-' . $brand->id . '.' . $extension;
            $logoPath = $request->file('logo')->storeAs('brands', $logoName, 'public');
            $brand->logo = $logoPath;
        }

        $brand->save();

        return back()->with('success', 'Brand updated successfully!');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Brand deleted successfully!');
    }
}