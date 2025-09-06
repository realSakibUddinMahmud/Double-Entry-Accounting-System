<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show all suppliers
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    // // Show create form
    // public function create()
    // {
    //     return view('admin.suppliers.create');
    // }

    // Store new supplier
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Supplier::create($request->all());

        // return redirect()->route('suppliers.index')
        //     ->with('success', 'Supplier created successfully.');
        return back()->with('success', 'Supplier added successfully!');
    }

    // // Show single supplier
    // public function show($id)
    // {
    //     $supplier = Supplier::findOrFail($id);
    //     return view('admin.suppliers.show', compact('supplier'));
    // }

    // // Show edit form
    // public function edit($id)
    // {
    //     $supplier = Supplier::findOrFail($id);
    //     return view('admin.suppliers.edit', compact('supplier'));
    // }

    // Update supplier
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'boolean', // Ensure status is boolean
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supplier->update($request->all());

        // return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
        return back()->with('success', 'Supplier updated successfully!');
    }

    // Delete supplier
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Check if the supplier has any purchases
        $hasPurchases = $supplier->purchases()->exists();

        // Check if the supplier has any accounts
        $hasAccounts = $supplier->accounts()->exists();

        // Check if any of the supplier's accounts have transactions
        $hasAccountTransactions = false;
        if ($hasAccounts) {
            foreach ($supplier->accounts()->get() as $account) {
                if ($account->transactions()->exists()) {
                    $hasAccountTransactions = true;
                    break;
                }
            }
        }

        if ($hasPurchases || $hasAccountTransactions) {
            // Don't delete, just archive
            $supplier->update(['status' => false]);
            return back()->with('warning', 'Supplier has purchases or account transactions. Status set to Archived instead of deleted.');
        }

        // If no purchases and no account transactions, delete the supplier
        $supplier->accounts()->delete(); // Delete associated accounts if any
        $supplier->delete();
        return back()->with('success', 'Supplier deleted successfully!');
    }
}