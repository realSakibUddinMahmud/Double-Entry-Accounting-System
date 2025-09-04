<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Category::with('parent')->orderBy('name');
        
        // Search by category name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Filter by parent category
        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'none') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }
        
        $categories = $query->paginate(20)->withQueryString();
        
        // Get parent categories for filter
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.category.index', compact('categories', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);


        if(Category::where('name', $request->name)->exists()) {
            return back()->withErrors(['name' => 'Category name already exists.']);
        }
        if ($request->parent_id && !Category::find($request->parent_id)) {
            return back()->withErrors(['parent_id' => 'Selected parent category does not exist.']);
        }
        // Create the category
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
        }

        Category::create([
            'name' => $request->name,
            'parent_id' => $parent->id ?? null,
        ]);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (Category::where('name', $request->name)->where('id', '!=', $category->id)->exists()) {
            return back()->withErrors(['name' => 'Category name already exists.']);
        }
        if ($request->parent_id && !Category::find($request->parent_id)) {
            return back()->withErrors(['parent_id' => 'Selected parent category does not exist.']);
        }

        $parent = null;
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
        }

        $category->update([
            'name' => $request->name,
            'parent_id' => $parent->id ?? null,
        ]);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // Check if the category has any children (leaf nodes)
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with subcategories.']);
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
}