<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'store'])->latest();
        
        // Search by invoice ID (u_id only)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('u_id', 'LIKE', "%{$search}%");
        }
        
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }
        
        $purchases = $query->paginate(20)->withQueryString();
        
        // Get suppliers and stores for filters
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $stores = \App\Models\Store::orderBy('name')->get();
        
        return view('admin.purchase.index', compact('purchases', 'suppliers', 'stores'));
    }

    public function create()
    {
        return view('admin.purchase.create');
    }

    public function store(Request $request)
    {
        // Validation and storing logic here
    }

    public function show(Purchase $purchase)
    {
        return view('admin.purchase.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        return view('admin.purchase.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        // Validation and update logic here
    }

    public function destroy(Purchase $purchase)
    {
        // Check if the purchase has any paid amount
        if ($purchase->paid_amount > 0) {
            return redirect()->route('purchases.index')->with('warning', 'Cannot delete purchase. Payment has already been made for this purchase.');
        }
        $purchase->journals()->each(function ($journal) {
            $journal->debitTransaction()->delete();
            $journal->creditTransaction()->delete();
            $journal->delete();
        });
        $purchase->items()->delete(); // Delete related purchase items
        $purchase->tasks()->each(function ($task) {
            $task->delete();
        });
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }
}