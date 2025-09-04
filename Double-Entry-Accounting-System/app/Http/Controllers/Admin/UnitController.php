<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Unit::with('parent')->orderBy('name');
        
        // Search by unit name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Filter by parent unit
        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'none') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }
        
        $units = $query->paginate(20)->withQueryString();
        
        // Get parent units for filter
        $parentUnits = Unit::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.unit.index', compact('units', 'parentUnits'));
    }

    public function store(Request $request)
    {
        $connection = app(\App\Models\Unit::class)->getConnectionName();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:50',
            'parent_id' => [
                'nullable',
                Rule::exists($connection . '.units', 'id'),
            ],
            'conversion_factor' => 'nullable|numeric|min:0.000001',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unit = new Unit();
        $unit->name = $request->name;
        $unit->symbol = $request->symbol;
        $unit->parent_id = $request->parent_id ?? null;
        $unit->conversion_factor = $request->conversion_factor ?? 1.0;
        $unit->save();

        return back()->with('success', 'Unit created successfully.');
    }

    public function update(Request $request, $id)
    {
        $connection = app(\App\Models\Unit::class)->getConnectionName();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:50',
            'parent_id' => [
                'nullable',
                Rule::exists($connection . '.units', 'id'),
            ],
            'conversion_factor' => 'nullable|numeric|min:0.000001',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unit = Unit::findOrFail($id);
        $unit->name = $request->name;
        $unit->symbol = $request->symbol;
        $unit->parent_id = $request->parent_id;
        $unit->conversion_factor = $request->conversion_factor;
        $unit->save();

        return back()->with('success', 'Unit updated successfully.');
    }

    public function destroy($id)
    {
        $unit = Unit::withCount('children')->findOrFail($id);
        if ($unit->children_count > 0) {
            return back()->withErrors(['error' => 'Cannot delete a unit with child units.']);
        }
        $unit->delete();
        return back()->with('success', 'Unit deleted successfully.');
    }
}
