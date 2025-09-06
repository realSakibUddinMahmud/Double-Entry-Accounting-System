<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $taxes = Tax::all();
        return view('admin.tax.index', compact('taxes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
        ]);

        // Handle toggle: if checkbox is checked, status=1, else 0
        $status = $request->has('status') ? 1 : 0;

        Tax::create([
            'name' => $request->name,
            'rate' => $request->rate,
            'status' => $status,
        ]);

        return redirect()->route('taxes.index')->with('success', 'Tax created successfully.');
    }

    public function update(Request $request, Tax $tax)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $tax->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'status' => $status,
        ]);

        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return back()->with('success', 'Tax deleted successfully.');
    }
}